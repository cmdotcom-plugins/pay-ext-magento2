<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use CM\Payments\Api\Data\CardDetailsInterface;
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
     * @param ClientPaymentCreateFactory $clientPaymentCreateFactory
     * @param PaymentCreateRequestFactory $paymentCreateRequestFactory
     * @param RequestPartInterface[] $partInterfaces
     */
    public function __construct(
        ClientPaymentCreateFactory $clientPaymentCreateFactory,
        PaymentCreateRequestFactory $paymentCreateRequestFactory,
        array $parts
    ) {
        $this->clientPaymentCreateFactory = $clientPaymentCreateFactory;
        $this->paymentCreateRequestFactory = $paymentCreateRequestFactory;
        $this->parts = $parts;
    }

    /**
     * @inheritDoc
     */
    public function create(
        string $orderId,
        string $orderKey,
        OrderInterface $order = null,
        CardDetailsInterface $cardDetails = null,
        BrowserDetailsInterface $browserDetails = null
    ): PaymentCreateRequest {
        /** @var PaymentCreate $paymentCreate */
        $paymentCreate = $this->clientPaymentCreateFactory->create();

        foreach ($this->parts as $part) {
            if ($part->needsOrder() && $order === null) {
                continue;
            }

            $paymentCreate = $part->process($paymentCreate, $order, $cardDetails, $browserDetails);
        }

        return $this->paymentCreateRequestFactory->create(
            [
                'orderKey' => $orderKey,
                'paymentCreate' => $paymentCreate
            ]
        );
    }
}
