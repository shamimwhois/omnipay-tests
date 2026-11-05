<?php

declare(strict_types=1);

namespace Omnipay\Tests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Common\Message\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

#[CoversClass(TestCase::class)]
final class TestCaseTest extends TestCase
{
    public function testCamelCase(): void
    {
        $this->assertSame('apiKey', $this->camelCase('api_key'));
        $this->assertSame('merchantId', $this->camelCase('merchant_id'));
        $this->assertSame('plain', $this->camelCase('plain'));
        $this->assertSame(['getApiKey', 'setApiKey'], $this->parameterAccessors('api_key'));
    }

    #[DataProvider('uniqueValueProvider')]
    public function testMakeUniqueValueRespectsDefaultType(string $key, mixed $default, string $expectedType): void
    {
        $value = $this->makeUniqueValue($key, $default);

        match ($expectedType) {
            'bool' => $this->assertIsBool($value),
            'int' => $this->assertIsInt($value),
            'float' => $this->assertIsFloat($value),
            'string' => $this->assertIsString($value),
            default => $this->fail('Unknown expected type: ' . $expectedType),
        };
    }

    /**
     * @return iterable<string, array{0: string, 1: mixed, 2: string}>
     */
    public static function uniqueValueProvider(): iterable
    {
        yield 'boolean' => ['testMode', false, 'bool'];
        yield 'integer' => ['merchantId', 0, 'int'];
        yield 'float' => ['rate', 1.5, 'float'];
        yield 'string' => ['apiKey', '', 'string'];
        yield 'currency' => ['currency', 'USD', 'string'];
    }

    public function testMakeUniqueValuePicksFromEnumeratedArray(): void
    {
        $options = ['us', 'eu', 'apac'];
        $value = $this->makeUniqueValue('region', $options);

        $this->assertContains($value, $options);
    }

    public function testMakeUniqueValueForEmptyArray(): void
    {
        $value = $this->makeUniqueValue('items', []);

        $this->assertIsArray($value);
        $this->assertNotEmpty($value);
        $this->assertIsString($value[0]);
    }

    public function testMakeUniqueValueForCurrencyIsUppercaseIsoCode(): void
    {
        $value = $this->makeUniqueValue('currency', 'usd');

        $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $value);
        $this->assertNotSame('USD', $value);
    }

    public function testExpectedParameterValueUppercasesCurrency(): void
    {
        $this->assertSame('EUR', $this->expectedParameterValue('currency', 'eur'));
        $this->assertSame('secret', $this->expectedParameterValue('apiKey', 'secret'));
    }

    public function testGetValidCardContainsRequiredFields(): void
    {
        $card = $this->getValidCard();

        $this->assertSame('4111111111111111', $card['number']);
        $this->assertArrayHasKey('billingAddress3', $card);
        $this->assertArrayHasKey('shippingAddress3', $card);
        $this->assertArrayHasKey('email', $card);
        $this->assertGreaterThanOrEqual(1, $card['expiryMonth']);
        $this->assertLessThanOrEqual(12, $card['expiryMonth']);
        $this->assertGreaterThan((int) gmdate('Y'), $card['expiryYear']);
    }

    public function testGetMockRequestReturnsRequestInterface(): void
    {
        $request = $this->getMockRequest();

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertSame($request, $this->getMockRequest());
    }

    public function testGetHttpClientAndRequestAreReusable(): void
    {
        $this->assertInstanceOf(ClientInterface::class, $this->getHttpClient());
        $this->assertSame($this->getHttpClient(), $this->getHttpClient());

        $this->assertInstanceOf(HttpRequest::class, $this->getHttpRequest());
        $this->assertSame($this->getHttpRequest(), $this->getHttpRequest());
    }

    public function testGetMockHttpResponseFromFile(): void
    {
        $response = $this->getMockHttpResponse('Success.txt');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('{"ok":true}', trim((string) $response->getBody()));
    }

    public function testGetMockHttpResponseAcceptsResponseInstance(): void
    {
        $original = new Response(204);
        $this->assertSame($original, $this->getMockHttpResponse($original));
    }

    public function testGetMockHttpResponseThrowsWhenMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getMockHttpResponse('DoesNotExist.txt');
    }

    public function testSetMockHttpResponseQueuesResponses(): void
    {
        $this->setMockHttpResponse(['Success.txt', new Response(201)]);

        $client = $this->getMockClient();
        $first = $client->sendRequest(new Request('GET', 'https://example.test/one'));
        $second = $client->sendRequest(new Request('GET', 'https://example.test/two'));

        $this->assertSame(200, $first->getStatusCode());
        $this->assertSame(201, $second->getStatusCode());
        $this->assertCount(2, $this->getMockedRequests());
    }
}
