<?php

declare(strict_types=1);

namespace Omnipay\Tests\Stubs;

use Omnipay\Common\Message\AbstractRequest;

class DummyRequest extends AbstractRequest
{
    public function getData(): array
    {
        return $this->getParameters();
    }

    public function sendData(mixed $data): DummyResponse
    {
        return $this->response = new DummyResponse($this, $data);
    }

    public function getApiKey(): mixed
    {
        return $this->getParameter('apiKey');
    }

    public function setApiKey(mixed $value): self
    {
        return $this->setParameter('apiKey', $value);
    }

    public function getMerchantId(): mixed
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId(mixed $value): self
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getRegion(): mixed
    {
        return $this->getParameter('region');
    }

    public function setRegion(mixed $value): self
    {
        return $this->setParameter('region', $value);
    }
}
