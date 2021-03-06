<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Payment;

use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Logger\CMPaymentsLogger;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Phrase;

class Redirect extends Action implements HttpGetActionInterface
{
    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

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
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * Redirect constructor
     *
     * @param Context $context
     * @param MessageManagerInterface $messageManager
     * @param Session $checkoutSession
     * @param RedirectFactory $redirectFactory
     * @param OrderServiceInterface $orderService
     * @param PaymentServiceInterface $paymentService
     * @param CMPaymentsLogger $logger
     */
    public function __construct(
        Context $context,
        MessageManagerInterface $messageManager,
        Session $checkoutSession,
        RedirectFactory $redirectFactory,
        OrderServiceInterface $orderService,
        PaymentServiceInterface $paymentService,
        CMPaymentsLogger $logger
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
        $this->logger = $logger;

        parent::__construct($context);
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
                throw new LocalizedException(__('No order id found.'));
            }

            $cmOrder = $this->orderService->create((int) $order->getEntityId());
            if (!$cmOrder->getOrderReference()) {
                throw new LocalizedException(__('The order was not placed properly.'));
            }

            $cmPayment = $this->paymentService->create((int) $order->getEntityId());
            $redirectUrl = $cmPayment->getRedirectUrl();
            if (!$redirectUrl) {
                throw new LocalizedException(__('No redirect url found in payment response'));
            }

            return $this->redirectFactory->create()
                ->setUrl($redirectUrl);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());

            return $this->redirectToCheckoutCart(__('Something went wrong while processing the order.'));
        }
    }

    /**
     * Return to cart with error message
     *
     * @param Phrase $message
     * @return ResultRedirect
     */
    private function redirectToCheckoutCart(Phrase $message): ResultRedirect
    {
        $this->checkoutSession->restoreQuote();

        $this->messageManager->addWarningMessage($message);

        return $this->redirectFactory->create()
            ->setPath('checkout/cart');
    }
}
