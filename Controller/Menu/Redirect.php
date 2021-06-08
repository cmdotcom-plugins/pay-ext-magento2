<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Menu;

use CM\Payments\Api\Service\OrderServiceInterface;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

class Redirect implements HttpGetActionInterface
{
    /**
     * @var Context
     */
    private $context;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Redirect constructor.
     * @param Session $checkoutSession
     * @param RedirectFactory $redirectFactory
     * @param OrderServiceInterface $orderService
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        RedirectFactory $redirectFactory,
        OrderServiceInterface $orderService,
        LoggerInterface $logger
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->orderService = $orderService;
        $this->logger = $logger;
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            $orderId = $order->getRealOrderId();

            $this->logger->debug(json_encode($order->getData()));
            $this->logger->debug($orderId);
            $this->logger->debug($order->getEntityId());
            if (!$orderId) {
                return $this->redirectToCheckoutCart(__('No order id found.'));
            }

            $cmOrder = $this->orderService->create($order->getEntityId());

            return $this->redirectFactory->create()
                ->setUrl($cmOrder->getUrl());
        } catch (Exception $exception) {
            $this->logger->error($exception);
            return $this->redirectToCheckoutCart(__('Something went wrong while creating the order.'));
        }
    }

    /**
     * Return to checkout cart with error message
     * @param Phrase $message
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function redirectToCheckoutCart(Phrase $message): \Magento\Framework\Controller\Result\Redirect
    {
        $this->checkoutSession->restoreQuote();

        $this->messageManager->addErrorMessage($message);

        return $this->redirectFactory->create()
            ->setPath('checkout/cart');
    }
}
