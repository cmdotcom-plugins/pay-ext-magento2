<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Payment;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Service\OrderTransactionServiceInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use CM\Payments\Logger\CMPaymentsLogger;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Phrase;
use Magento\Sales\Api\OrderManagementInterface;

class Result extends Action implements HttpGetActionInterface
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
    protected $messageManager;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var OrderTransactionServiceInterface
     */
    private $orderTransactionService;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Result constructor

     * @param Context $context
     * @param RequestInterface $request,
     * @param MessageManagerInterface $messageManager,
     * @param CheckoutSession $checkoutSession
     * @param RedirectFactory $redirectFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderTransactionServiceInterface $orderTransactionService
     * @param ConfigInterface $config
     * @param CMPaymentsLogger $logger
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        MessageManagerInterface $messageManager,
        CheckoutSession $checkoutSession,
        RedirectFactory $redirectFactory,
        OrderManagementInterface $orderManagement,
        OrderTransactionServiceInterface $orderTransactionService,
        ConfigInterface $config,
        CMPaymentsLogger $logger
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->redirectFactory = $redirectFactory;
        $this->orderManagement = $orderManagement;
        $this->orderTransactionService = $orderTransactionService;
        $this->config = $config;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $referenceOrderId = $this->request->getParam('order_reference');
        $status = $this->request->getParam('status');

        try {
            if (!$status) {
                throw new LocalizedException(__('The Status is not presented in response!'));
            }

            if (!$referenceOrderId) {
                throw new LocalizedException(__('The order reference is not valid!'));
            }

            if (in_array($status, [OrderCreate::STATUS_ERROR, OrderCreate::STATUS_CANCELLED])) {
                if ($status === OrderCreate::STATUS_ERROR) {
                    throw new LocalizedException(__('Your payment was cancelled because of errors!'));
                } else {
                    throw new LocalizedException(__('Your payment was cancelled!'));
                }
            }

            // In this state we always redirect the user to the success page and try to update the order status already.
            // The order status will also be updated via the webhook if CM.com change the status of an order.
            try {
                if ($this->config->isUpdateOnResultPageEnabled()) {
                    $this->orderTransactionService->process($referenceOrderId);
                }
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }

            return $this->redirectToSuccessPage($referenceOrderId);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());

            return $this->redirectToCheckoutCart(__('Something went wrong while processing the order.'));
        }
    }

    /**
     * Return to cart with error message
     *
     * @param Phrase $message
     * @return Redirect
     */
    private function redirectToCheckoutCart(Phrase $message): Redirect
    {
        $customReturnUrl = $this->config->getCustomerErrorUrl();

        if (!$customReturnUrl) {
            $this->checkoutSession->restoreQuote();
            $this->messageManager->addWarningMessage($message);
        }

        $redirectParams = '?utm_nooverride=1';
        $returnUrl = $customReturnUrl ?? 'checkout/cart';

        return $this->redirectFactory->create()
            ->setPath($returnUrl . $redirectParams);
    }

    /**
     * @param string $orderIncrementId
     * @return Redirect
     */
    private function redirectToSuccessPage(string $orderIncrementId): Redirect
    {
        $redirectParams = '?utm_nooverride=1';

        if ($this->config->getCustomerSuccessUrl()) {
            $redirectParams .= '&order_increment_id=' . $orderIncrementId;
        }

        $returnUrl = $this->config->getCustomerSuccessUrl() ?? 'checkout/onepage/success';

        return $this->redirectFactory->create()
            ->setPath($returnUrl . $redirectParams);
    }
}
