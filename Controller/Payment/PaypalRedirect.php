<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Payment;

use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Client\Model\CMPaymentUrl;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

class PaypalRedirect implements HttpGetActionInterface
{
    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * @var PaymentServiceInterface
     */
    private $paymentService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PaypalRedirect constructor
     *
     * @param MessageManagerInterface $messageManager
     * @param Session $checkoutSession
     * @param RedirectFactory $redirectFactory
     * @param OrderServiceInterface $orderService
     * @param PaymentServiceInterface $paymentService
     * @param LoggerInterface $logger
     */
    public function __construct(
        MessageManagerInterface $messageManager,
        Session $checkoutSession,
        RedirectFactory $redirectFactory,
        OrderServiceInterface $orderService,
        PaymentServiceInterface $paymentService,
        LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            $orderId = $order->getRealOrderId();

            if (!$orderId) {
                return $this->redirectToCheckoutCart(__('No order id found.'));
            }

            $cmOrder = $this->orderService->create($order->getEntityId());
            $cmPayment = $this->paymentService->create($order->getEntityId());
            $url = $this->getUrl($cmPayment->getUrls());
            if (empty($url)) {
                throw new LocalizedException(__('No redirect url found in payment response'));
            }

            return $this->redirectFactory->create()
                ->setUrl($url);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
            return $this->redirectToCheckoutCart(__('Something went wrong while creating the order.'));
        }
    }

    /**
     * Return to checkout cart with error message
     *
     * @param Phrase $message
     * @return Redirect
     */
    public function redirectToCheckoutCart(Phrase $message): Redirect
    {
        $this->checkoutSession->restoreQuote();

        $this->messageManager->addErrorMessage($message);

        return $this->redirectFactory->create()
            ->setPath('checkout/cart');
    }

    /**
     * @param CMPaymentUrl[] $paymentUrls
     *
     * @return string
     */
    private function getUrl(array $paymentUrls): string
    {
        foreach ($paymentUrls as $paymentUrl) {
            if ($paymentUrl->getPurpose() === CMPaymentUrl::PURPOSE_REDIRECT) {
                return $paymentUrl->getUrl();
            }
        }

        return '';
    }
}
