<?php

declare(strict_types=1);

namespace Omnipay\Tests;

use Omnipay\Tests\Stubs\DummyGateway;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GatewayTestCase::class)]
final class GatewayTestCaseTest extends GatewayTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new DummyGateway($this->getHttpClient(), $this->getHttpRequest());
    }
}
