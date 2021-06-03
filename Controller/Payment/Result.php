<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Payment;

use CM\Payments\Api\Service\OrderServiceInterface;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Psr\Log\LoggerInterface;

class Result implements HttpPostActionInterface
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
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Result constructor
     *
     * @param CheckoutSession $checkoutSession
     * @param RedirectFactory $redirectFactory
     * @param OrderServiceInterface $orderService
     * @param LoggerInterface $logger
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        RedirectFactory $redirectFactory,
        OrderServiceInterface $orderService,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->orderService = $orderService;
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

            $this->logger->debug(json_encode($order->getData()));
            $this->logger->debug($orderId);
            $this->logger->debug($order->getEntityId());
            if (!$orderId) {
                return $this->redirectToCheckout();
            }

            $orderRedirectUrl = $this->orderService->create($order->getEntityId());

            return $this->redirectFactory->create()
                ->setUrl($orderRedirectUrl);
        } catch (Exception $exception) {
            // Todo: show error message

            $this->logger->error($exception);
            return $this->redirectToCheckout();
        }
    }

    public function redirectToCheckout(): Redirect
    {
        $this->checkoutSession->restoreQuote();

        // Todo: show error message
        return $this->redirectFactory->create()
            ->setPath('checkout/cart');
    }
}
