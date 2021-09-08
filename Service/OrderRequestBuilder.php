<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Service\Order\Request\RequestPartByOrderInterface;
use CM\Payments\Api\Service\Order\Request\RequestPartByQuoteInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use CM\Payments\Client\Model\Request\OrderCreateFactory as ClientOrderCreateFactory;
use CM\Payments\Client\Request\OrderCreateRequest;
use CM\Payments\Client\Request\OrderCreateRequestFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderRequestBuilder implements OrderRequestBuilderInterface
{
    /**
     * @var ClientOrderCreateFactory
     */
    private $clientOrderCreateFactory;

    /**
     * @var OrderCreateRequestFactory
     */
    private $orderCreateRequestFactory;

    /**
     * @var RequestPartByOrderInterface[]
     */
    private $orderRequestParts;

    /**
     * @var RequestPartByQuoteInterface[]
     */
    private $quoteRequestParts;

    /**
     * OrderRequestBuilder constructor
     *
     * @param ClientOrderCreateFactory $clientOrderCreateFactory
     * @param OrderCreateRequestFactory $orderCreateRequestFactory
     * @param RequestPartByOrderInterface[] $orderRequestParts
     * @param RequestPartByQuoteInterface[] $quoteRequestParts
     */
    public function __construct(
        ClientOrderCreateFactory $clientOrderCreateFactory,
        OrderCreateRequestFactory $orderCreateRequestFactory,
        array $orderRequestParts,
        array $quoteRequestParts
    ) {
        $this->clientOrderCreateFactory = $clientOrderCreateFactory;
        $this->orderCreateRequestFactory = $orderCreateRequestFactory;
        $this->orderRequestParts = $orderRequestParts;
        $this->quoteRequestParts = $quoteRequestParts;
    }

    /**
     * @inheritDoc
     */
    public function create(OrderInterface $order): OrderCreateRequest
    {
        /** @var OrderCreate $orderCreate */
        $orderCreate = $this->clientOrderCreateFactory->create();

        foreach ($this->orderRequestParts as $part) {
            $orderCreate = $part->process($order, $orderCreate);
        }

        return $this->orderCreateRequestFactory->create(['orderCreate' => $orderCreate]);
    }

    /**
     * @inheritDoc
     */
    public function createByQuote(CartInterface $quote): OrderCreateRequest
    {
        /** @var OrderCreate $orderCreate */
        $orderCreate = $this->clientOrderCreateFactory->create();

        foreach ($this->quoteRequestParts as $part) {
            $orderCreate = $part->process($quote, $orderCreate);
        }

        return $this->orderCreateRequestFactory->create(['orderCreate' => $orderCreate]);
    }
}
