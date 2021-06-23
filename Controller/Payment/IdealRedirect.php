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
use CM\Payments\Exception\NoRedirectUrlProvidedException;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

class IdealRedirect implements HttpGetActionInterface
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
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var PaymentServiceInterface
     */
    private $paymentService;
    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * Redirect constructor.
     * @param MessageManagerInterface $messageManager
     * @param Session $checkoutSession
     * @param RedirectFactory $redirectFactory
     * @param OrderServiceInterface $orderService
     * @param LoggerInterface $logger
     */
    public function __construct(
        MessageManagerInterface $messageManager,
        Session $checkoutSession,
        RedirectFactory $redirectFactory,
        PaymentServiceInterface $paymentService,
        OrderServiceInterface $orderService,
        LoggerInterface $logger
    ) {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->logger = $logger;
        $this->paymentService = $paymentService;
        $this->orderService = $orderService;
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
            // Todo: use cmOrder->getOrderKey() to create payment instead of Magento orderId
            $cmPayment = $this->paymentService->create($order->getEntityId());

            $url = $this->getUrl($cmPayment->getUrls());
            if (empty($url)) {
                // Todo: log
                //[
                //                    'orderId' => $order->getEntityId(),
                //                    'paymentId' => $cmPayment->getId()
                //                ]
                throw new NoRedirectUrlProvidedException(__('No redirect url found in payment response'));
            }

            return $this->redirectFactory->create()
                ->setUrl($url);
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
    private function redirectToCheckoutCart(Phrase $message): \Magento\Framework\Controller\Result\Redirect
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
