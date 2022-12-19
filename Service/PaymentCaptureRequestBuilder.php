<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Service\Payment\Capture\Request\RequestPartInterface;
use CM\Payments\Api\Service\PaymentCaptureRequestBuilderInterface;
use CM\Payments\Client\Model\Request\PaymentCaptureCreate;
use CM\Payments\Client\Model\Request\PaymentCaptureCreateFactory as ClientPaymentCaptureCreateFactory;
use CM\Payments\Client\Request\PaymentCaptureCreateRequest;
use CM\Payments\Client\Request\PaymentCaptureCreateRequestFactory;
use Magento\Sales\Api\Data\OrderInterface;

class PaymentCaptureRequestBuilder implements PaymentCaptureRequestBuilderInterface
{
    /**
     * @var ClientPaymentCaptureCreateFactory
     */
    private $clientPaymentCaptureCreateFactory;

    /**
     * @var PaymentCaptureCreateRequestFactory
     */
    private $paymentCaptureCreateRequestFactory;

    /**
     * @var RequestPartInterface[]
     */
    private $parts;

    /**
     * PaymentRequestBuilder constructor
     *
     * @param ClientPaymentCaptureCreateFactory $clientPaymentCaptureCreateFactory
     * @param PaymentCaptureCreateRequestFactory $paymentCaptureCreateRequestFactory
     * @param RequestPartInterface[] $parts
     */
    public function __construct(
        ClientPaymentCaptureCreateFactory $clientPaymentCaptureCreateFactory,
        PaymentCaptureCreateRequestFactory $paymentCaptureCreateRequestFactory,
        array $parts
    ) {
        $this->clientPaymentCaptureCreateFactory = $clientPaymentCaptureCreateFactory;
        $this->paymentCaptureCreateRequestFactory = $paymentCaptureCreateRequestFactory;
        $this->parts = $parts;
    }

    /**
     * @inheritDoc
     */
    public function create(
        string $orderKey,
        string $paymentId,
        OrderInterface $order
    ): PaymentCaptureCreateRequest {
        /** @var PaymentCaptureCreate $paymentCaptureCreate */
        $paymentCaptureCreate = $this->clientPaymentCaptureCreateFactory->create();

        foreach ($this->parts as $part) {
            $paymentCaptureCreate = $part->process($paymentCaptureCreate, $order);
        }

        return $this->paymentCaptureCreateRequestFactory->create(
            [
                'orderKey' => $orderKey,
                'paymentId' => $paymentId,
                'paymentCaptureCreate' => $paymentCaptureCreate
            ]
        );
    }
}
