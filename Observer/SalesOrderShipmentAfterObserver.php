<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Observer;

use CM\Payments\Api\Service\PaymentServiceInterface;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class SalesOrderShipmentAfterObserver extends AbstractDataAssignObserver
{
    /**
     * @var PaymentServiceInterface
     */
    private $paymentService;

    /**
     * @param PaymentServiceInterface $paymentService
     */
    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();

        $this->paymentService->captureKlarnaPayment($order);
    }
}
