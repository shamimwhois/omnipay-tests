<?php

declare(strict_types=1);

namespace Omnipay\Tests;

use GuzzleHttp\Psr7\Message;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Strategy\MockClientStrategy;
use Http\Mock\Client as MockClient;
use InvalidArgumentException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Omnipay\Common\Http\Client;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Common\Message\RequestInterface;
use Override;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionObject;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

/**
 * Base class for all Omnipay tests
 */
abstract class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;

    private static bool $mockStrategyRegistered = false;

    private ?RequestInterface $mockRequest = null;

    private ?MockClient $mockClient = null;

    private ?ClientInterface $httpClient = null;

    private ?HttpRequest $httpRequest = null;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$mockStrategyRegistered) {
            HttpClientDiscovery::prependStrategy(MockClientStrategy::class);
            self::$mockStrategyRegistered = true;
        }
    }

    /**
     * Converts a string to camel case
     */
    public function camelCase(string $str): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
    }

    /**
     * Build a unique sample value that matches the default parameter type.
     *
     * Boolean, integer, float and enumerated array defaults no longer receive a
     * random string (which caused false failures). Currency is always a valid
     * uppercase ISO code because AbstractGateway::getCurrency() uppercases.
     */
    public function makeUniqueValue(string $key, mixed $default): mixed
    {
        if (strcasecmp($key, 'currency') === 0) {
            return $this->makeUniqueCurrency($default);
        }

        return match (true) {
            is_bool($default) => !$default,
            is_int($default) => random_int(100000, 999999),
            is_float($default) => random_int(1000, 9999) / 100,
            is_array($default) && $default !== [] => $default[array_rand($default)],
            is_array($default) => ['item_' . uniqid('', true)],
            default => uniqid('', true),
        };
    }

    /**
     * Normalize a value to what a typical Omnipay getter will return.
     */
    public function expectedParameterValue(string $key, mixed $value): mixed
    {
        if (strcasecmp($key, 'currency') === 0 && is_string($value)) {
            return strtoupper($value);
        }

        return $value;
    }

    /**
     * Get all of the mocked requests
     *
     * @return list<\Psr\Http\Message\RequestInterface>
     */
    public function getMockedRequests(): array
    {
        return $this->getMockClient()->getRequests();
    }

    /**
     * Get a mock response for a client by mock file name
     */
    public function getMockHttpResponse(string|ResponseInterface $path): ResponseInterface
    {
        if ($path instanceof ResponseInterface) {
            return $path;
        }

        $dir = dirname((new ReflectionObject($this))->getFileName() ?: '');
        $file = array_find(
            [$dir . '/Mock/' . $path, $dir . '/../Mock/' . $path],
            static fn (string $candidate): bool => is_file($candidate)
        );

        if ($file === null) {
            throw new InvalidArgumentException(sprintf('Mock HTTP response file not found: %s', $path));
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new RuntimeException(sprintf('Unable to read mock HTTP response file: %s', $file));
        }

        return Message::parseResponse($contents);
    }

    /**
     * Set a mock response from a mock file on the next client request.
     *
     * This method assumes that mock response files are located under the
     * Mock/ subdirectory of the current class. A mock response is added to the next
     * request sent by the client.
     *
     * An array of paths can be provided and the next x number of client requests are
     * mocked in the order of the array where x = the array length.
     *
     * @param array<int, string|ResponseInterface>|string|ResponseInterface $paths
     */
    public function setMockHttpResponse(array|string|ResponseInterface $paths): void
    {
        foreach (is_array($paths) ? $paths : [$paths] as $path) {
            $this->getMockClient()->addResponse($this->getMockHttpResponse($path));
        }
    }

    /**
     * Helper method used by gateway test classes to generate a valid test credit card
     *
     * @return array<string, mixed>
     */
    public function getValidCard(): array
    {
        return [
            'firstName' => 'Example',
            'lastName' => 'User',
            'number' => '4111111111111111',
            'expiryMonth' => random_int(1, 12),
            'expiryYear' => ((int) gmdate('Y')) + random_int(1, 5),
            'cvv' => random_int(100, 999),
            'email' => 'example.user@example.com',
            'billingAddress1' => '123 Billing St',
            'billingAddress2' => 'Billsville',
            'billingAddress3' => 'Building B, Room 308',
            'billingCity' => 'Billstown',
            'billingPostcode' => '12345',
            'billingState' => 'CA',
            'billingCountry' => 'US',
            'billingPhone' => '(555) 123-4567',
            'shippingAddress1' => '123 Shipping St',
            'shippingAddress2' => 'Shipsville',
            'shippingAddress3' => 'Building S, Room 114',
            'shippingCity' => 'Shipstown',
            'shippingPostcode' => '54321',
            'shippingState' => 'NY',
            'shippingCountry' => 'US',
            'shippingPhone' => '(555) 987-6543',
        ];
    }

    public function getMockRequest(): RequestInterface
    {
        return $this->mockRequest ??= m::mock(RequestInterface::class);
    }

    public function getMockClient(): MockClient
    {
        return $this->mockClient ??= new MockClient();
    }

    public function getHttpClient(): ClientInterface
    {
        return $this->httpClient ??= new Client($this->getMockClient());
    }

    public function getHttpRequest(): HttpRequest
    {
        return $this->httpRequest ??= new HttpRequest();
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function parameterAccessors(string $key): array
    {
        $name = ucfirst($this->camelCase($key));

        return ['get' . $name, 'set' . $name];
    }

    private function makeUniqueCurrency(mixed $default): string
    {
        $currencies = ['USD', 'EUR', 'GBP', 'AUD', 'CAD', 'JPY', 'NZD', 'CHF'];

        if (is_array($default) && $default !== []) {
            $choice = $default[array_rand($default)];

            return is_string($choice) ? strtoupper($choice) : 'USD';
        }

        if (is_string($default) && $default !== '') {
            $current = strtoupper($default);

            return array_find(
                $currencies,
                static fn (string $code): bool => $code !== $current
            ) ?? $currencies[0];
        }

        return $currencies[array_rand($currencies)];
    }
}
