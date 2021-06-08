<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Payment;

use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Psr\Log\LoggerInterface;
use CM\Payments\Client\Model\Order;

class Result implements ActionInterface, HttpGetActionInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Result constructor
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param RedirectFactory $redirectFactory
     * @param OrderManagementInterface $orderManagement
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        RedirectFactory $redirectFactory,
        OrderManagementInterface $orderManagement,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->request = $context->getRequest();
        $this->messageManager = $context->getMessageManager();
        $this->orderManagement = $orderManagement;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $referenceOrderId = $this->request->getParam('order_reference');
        $status = $this->request->getParam('status');

        try {
            if (!$referenceOrderId || !$status) {
                $this->messageManager->addErrorMessage(__("The order reference is not valid!"));

                return $this->redirectToCheckout();
            }

            $orderIncrementId = $this->checkoutSession->getLastRealOrder()->getIncrementId();
            if (!$orderIncrementId || $referenceOrderId !== $orderIncrementId) {
                $this->messageManager->addErrorMessage(__("The order reference is not valid!"));

                return $this->redirectToCheckout();
            }

            if (in_array($status, [Order::STATUS_ERROR, Order::STATUS_CANCELLED])) {
                $this->orderManagement->cancel($this->checkoutSession->getLastRealOrder()->getId());
                $this->messageManager->addErrorMessage(__("The order was cancelled because of payment errors!"));

                return $this->redirectToCheckout();
            };

            return $this->redirectFactory->create()
                ->setPath('checkout/onepage/success');
        } catch (Exception $exception) {
            $this->logger->error($exception);
            $this->messageManager->addErrorMessage($exception->getMessage());

            return $this->redirectToCheckout();
        }
    }

    /**
     * @return Redirect
     */
    public function redirectToCheckout(): Redirect
    {
        $this->checkoutSession->restoreQuote();

        return $this->redirectFactory->create()
            ->setPath('checkout/cart');
    }
}
