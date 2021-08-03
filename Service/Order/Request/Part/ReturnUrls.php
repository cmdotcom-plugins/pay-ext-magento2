<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Order\Request\Part;

use CM\Payments\Api\Service\Order\Request\RequestPartByOrderInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use CM\Payments\Client\Model\Request\OrderCreate as ClientOrder;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;

class ReturnUrls implements RequestPartByOrderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * ReturnUrls constructor
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function process(OrderInterface $order, OrderCreate $orderCreate): OrderCreate
    {
        $orderCreate->setReturnUrls([
            'success' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_SUCCESS),
            'pending' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_PENDING),
            'canceled' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_CANCELLED),
            'error' => $this->getReturnUrl($order->getIncrementId(), ClientOrder::STATUS_ERROR)
        ]);

        return $orderCreate;
    }

    /**
     * Get Return Url
     *
     * @param string $orderReference
     * @param string $status
     * @return string
     */
    private function getReturnUrl(string $orderReference, string $status): string
    {
        return $this->urlBuilder->getUrl('cmpayments/payment/result', [
            '_query' => [
                'order_reference' => $orderReference,
                'status' => $status
            ]
        ]);
    }
}
