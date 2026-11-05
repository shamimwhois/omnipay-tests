<?php

declare(strict_types=1);

namespace Omnipay\Tests\Stubs;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\RequestInterface;

class DummyGateway extends AbstractGateway
{
    public function getName(): string
    {
        return 'Dummy';
    }

    public function getDefaultParameters(): array
    {
        return [
            'apiKey' => '',
            'merchantId' => 0,
            'testMode' => false,
            'currency' => 'USD',
            'region' => ['us', 'eu', 'apac'],
        ];
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

    public function purchase(array $parameters = []): RequestInterface
    {
        return $this->createRequest(DummyRequest::class, $parameters);
    }

    public function authorize(array $parameters = []): RequestInterface
    {
        return $this->createRequest(DummyRequest::class, $parameters);
    }
}
