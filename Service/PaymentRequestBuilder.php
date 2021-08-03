<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Service\Payment\Request\RequestPartInterface;
use CM\Payments\Api\Service\PaymentRequestBuilderInterface;
use CM\Payments\Client\Model\Request\PaymentCreate;
use CM\Payments\Client\Model\Request\PaymentCreateFactory as ClientPaymentCreateFactory;
use CM\Payments\Client\Request\PaymentCreateRequest;
use CM\Payments\Client\Request\PaymentCreateRequestFactory;
use Magento\Sales\Api\Data\OrderInterface;

class PaymentRequestBuilder implements PaymentRequestBuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ClientPaymentCreateFactory
     */
    private $clientPaymentCreateFactory;

    /**
     * @var PaymentCreateRequestFactory
     */
    private $paymentCreateRequestFactory;

    /**
     * @var RequestPartInterface[]
     */
    private $parts;

    /**
     * PaymentRequestBuilder constructor
     *
     * @param ConfigInterface $config
     * @param ClientPaymentCreateFactory $clientPaymentCreateFactory
     * @param PaymentCreateRequestFactory $paymentCreateRequestFactory
     * @param RequestPartInterface[] $partInterfaces
     */
    public function __construct(
        ConfigInterface $config,
        ClientPaymentCreateFactory $clientPaymentCreateFactory,
        PaymentCreateRequestFactory $paymentCreateRequestFactory,
        array $parts
    ) {
        $this->config = $config;
        $this->clientPaymentCreateFactory = $clientPaymentCreateFactory;
        $this->paymentCreateRequestFactory = $paymentCreateRequestFactory;
        $this->parts = $parts;
    }

    /**
     * @inheritDoc
     */
    public function create(OrderInterface $order, string $orderKey): PaymentCreateRequest
    {
        /** @var PaymentCreate $paymentCreate */
        $paymentCreate = $this->clientPaymentCreateFactory->create();

        foreach ($this->parts as $part) {
            $paymentCreate = $part->process($order, $paymentCreate);
        }

        return $this->paymentCreateRequestFactory->create(
            [
                'orderKey' => $orderKey,
                'paymentCreate' => $paymentCreate
            ]
        );
    }
}
