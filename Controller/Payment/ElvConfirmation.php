<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Payment;

use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

class ElvConfirmation extends Action implements HttpGetActionInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ElvConfirmation constructor
     *
     * @param Context $context
     * @param MessageManagerInterface $messageManager
     * @param Session $checkoutSession
     * @param RedirectFactory $redirectFactory
     * @param OrderServiceInterface $orderService
     * @param PaymentServiceInterface $paymentService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
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
                return $this->redirectToCheckoutCart(__('No order id found.'));
            }

            $cmOrder = $this->orderService->create($order->getEntityId());
            if (!$cmOrder->getOrderReference()) {
                throw new LocalizedException(__('The order was not placed properly.'));
            }

            $cmPayment = $this->paymentService->create($order->getEntityId());
            if (!$cmPayment->getId()) {
                throw new LocalizedException(__('The Payment was not finished properly!'));
            }

            return $this->redirectFactory->create()
                ->setPath('checkout/onepage/success');
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
}
