<?php

declare(strict_types=1);

namespace CM\Payments\Observer;

use CM\Payments\Config\Config;
use CM\Payments\Model\ConfigProvider;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Invoice;

class SendOrderEmailAfterInvoicePay implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @param Config $config
     * @param OrderSender $orderSender
     */
    public function __construct(
        Config $config,
        OrderSender $orderSender
    ) {
        $this->config = $config;
        $this->orderSender = $orderSender;
    }

    /**
     * Observer for sales_order_invoice_pay
     *
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->config->isSendOrderEmailForPaid()) {
            return;
        }

        $event = $observer->getEvent();
        /** @var Invoice $invoice */
        $invoice = $event->getInvoice();
        $order = $invoice->getOrder();

        if (\strpos($order->getPayment()->getMethod(), ConfigProvider::CODE) !== false) {
            $this->orderSender->send($order);
        }
    }
}
