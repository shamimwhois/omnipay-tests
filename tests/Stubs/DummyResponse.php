<?php

declare(strict_types=1);

namespace Omnipay\Tests\Stubs;

use Omnipay\Common\Message\AbstractResponse;

class DummyResponse extends AbstractResponse
{
    public function isSuccessful(): bool
    {
        return true;
    }
}
