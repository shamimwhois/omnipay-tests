<?php

declare(strict_types=1);

namespace Omnipay\Tests;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\RequestInterface;

/**
 * Base Gateway Test class
 *
 * Ensures all gateways conform to consistent standards
 */
abstract class GatewayTestCase extends TestCase
{
    protected AbstractGateway $gateway;

    public function testGetNameNotEmpty(): void
    {
        $name = $this->gateway->getName();
        $this->assertNotEmpty($name);
        $this->assertIsString($name);
    }

    public function testGetShortNameNotEmpty(): void
    {
        $shortName = $this->gateway->getShortName();
        $this->assertNotEmpty($shortName);
        $this->assertIsString($shortName);
    }

    public function testGetDefaultParametersReturnsArray(): void
    {
        $this->assertIsArray($this->gateway->getDefaultParameters());
    }

    public function testDefaultParametersHaveMatchingMethods(): void
    {
        foreach ($this->gateway->getDefaultParameters() as $key => $default) {
            [$getter, $setter] = $this->parameterAccessors($key);
            $value = $this->makeUniqueValue($key, $default);

            $this->assertTrue(method_exists($this->gateway, $getter), "Gateway must implement {$getter}()");
            $this->assertTrue(method_exists($this->gateway, $setter), "Gateway must implement {$setter}()");

            $this->assertSame($this->gateway, $this->gateway->{$setter}($value));
            $this->assertSame($this->expectedParameterValue($key, $value), $this->gateway->{$getter}());
        }
    }

    public function testTestMode(): void
    {
        $this->assertSame($this->gateway, $this->gateway->setTestMode(false));
        $this->assertFalse($this->gateway->getTestMode());

        $this->assertSame($this->gateway, $this->gateway->setTestMode(true));
        $this->assertTrue($this->gateway->getTestMode());
    }

    public function testCurrency(): void
    {
        $this->assertSame($this->gateway, $this->gateway->setCurrency('eur'));
        $this->assertSame('EUR', $this->gateway->getCurrency());
    }

    public function testSupportsAuthorize(): void
    {
        $this->assertGatewaySupports('authorize');
    }

    public function testSupportsCompleteAuthorize(): void
    {
        $this->assertGatewaySupports('completeAuthorize');
    }

    public function testSupportsCapture(): void
    {
        $this->assertGatewaySupports('capture');
    }

    public function testSupportsPurchase(): void
    {
        $this->assertGatewaySupports('purchase');
    }

    public function testSupportsCompletePurchase(): void
    {
        $this->assertGatewaySupports('completePurchase');
    }

    public function testSupportsRefund(): void
    {
        $this->assertGatewaySupports('refund');
    }

    public function testSupportsVoid(): void
    {
        $this->assertGatewaySupports('void');
    }

    public function testSupportsCreateCard(): void
    {
        $this->assertGatewaySupports('createCard');
    }

    public function testSupportsDeleteCard(): void
    {
        $this->assertGatewaySupports('deleteCard');
    }

    public function testSupportsUpdateCard(): void
    {
        $this->assertGatewaySupports('updateCard');
    }

    public function testSupportsFetchTransaction(): void
    {
        $this->assertGatewaySupports('fetchTransaction');
    }

    public function testSupportsAcceptNotification(): void
    {
        $supports = $this->gateway->supportsAcceptNotification();
        $this->assertIsBool($supports);
        $this->assertSame($supports, method_exists($this->gateway, 'acceptNotification'));
    }

    public function testAuthorizeParameters(): void
    {
        $this->assertRequestParametersMatchGateway('authorize');
    }

    public function testCompleteAuthorizeParameters(): void
    {
        $this->assertRequestParametersMatchGateway('completeAuthorize');
    }

    public function testCaptureParameters(): void
    {
        $this->assertRequestParametersMatchGateway('capture');
    }

    public function testPurchaseParameters(): void
    {
        $this->assertRequestParametersMatchGateway('purchase');
    }

    public function testCompletePurchaseParameters(): void
    {
        $this->assertRequestParametersMatchGateway('completePurchase');
    }

    public function testRefundParameters(): void
    {
        $this->assertRequestParametersMatchGateway('refund');
    }

    public function testVoidParameters(): void
    {
        $this->assertRequestParametersMatchGateway('void');
    }

    public function testCreateCardParameters(): void
    {
        $this->assertRequestParametersMatchGateway('createCard');
    }

    public function testDeleteCardParameters(): void
    {
        $this->assertRequestParametersMatchGateway('deleteCard');
    }

    public function testUpdateCardParameters(): void
    {
        $this->assertRequestParametersMatchGateway('updateCard');
    }

    public function testFetchTransactionParameters(): void
    {
        $this->assertRequestParametersMatchGateway('fetchTransaction');
    }

    protected function assertGatewaySupports(string $method): void
    {
        $supports = $this->gateway->{'supports' . ucfirst($method)}();
        $this->assertIsBool($supports);

        if ($supports) {
            $this->assertInstanceOf(RequestInterface::class, $this->gateway->{$method}());
            return;
        }

        $this->assertFalse(method_exists($this->gateway, $method));
    }

    protected function assertRequestParametersMatchGateway(string $method): void
    {
        if (!$this->gateway->{'supports' . ucfirst($method)}()) {
            $this->expectNotToPerformAssertions();
            return;
        }

        foreach ($this->gateway->getDefaultParameters() as $key => $default) {
            [$getter, $setter] = $this->parameterAccessors($key);
            $value = $this->makeUniqueValue($key, $default);
            $this->gateway->{$setter}($value);

            $request = $this->gateway->{$method}();
            $this->assertSame($this->expectedParameterValue($key, $value), $request->{$getter}());
        }
    }
}
