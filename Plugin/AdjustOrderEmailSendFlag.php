<?php

declare(strict_types=1);

namespace CM\Payments\Plugin;

use CM\Payments\Config\Config;
use CM\Payments\Model\ConfigProvider;
use Magento\Framework\Event\Observer;
use Magento\Quote\Observer\SubmitObserver;
use Magento\Sales\Model\Order;

/**
 * Send admin order confirmation
 */
class AdjustOrderEmailSendFlag
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Adjusts order flag to not send email for CM Payments orders which are not yet paid
     *
     * @param SubmitObserver $subject
     * @param Observer $observer
     * @return Observer[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(SubmitObserver $subject, Observer $observer): array
    {
        if (!$this->config->isSendOrderEmailForPaid()) {
            return [$observer];
        }

        /** @var  Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var  Order\Payment $payment */
        $payment = $order->getPayment();

        if (\strpos($payment->getMethod(), ConfigProvider::CODE) !== false) {
            $order->setCanSendNewEmailFlag(false);
        }

        return [$observer];
    }
}
