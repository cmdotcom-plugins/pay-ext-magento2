<?php

namespace CM\Payments\Api\Service;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;

interface RefundServiceInterface
{
    /**
     * @param Creditmemo $creditmemo
     * @return void
     */
    public function createOrderRefund(Creditmemo $creditmemo): void;
}
