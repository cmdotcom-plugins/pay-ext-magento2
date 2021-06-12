<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Payment;

use CM\Payments\Api\Model\Data\OrderInterface as CMOrderInterface;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Psr\Log\LoggerInterface;

class Notification implements HttpGetActionInterface, CsrfAwareActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CMOrderRepositoryInterface
     */
    private $cmOrderRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Notification constructor
     *
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param CMOrderRepositoryInterface $cmOrderRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderServiceInterface $orderService,
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        CMOrderRepositoryInterface $cmOrderRepository,
        OrderRepositoryInterface $orderRepository,
        OrderServiceInterface $orderService,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
        $this->logger = $logger;
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultPage */
        $resultPage = $this->resultJsonFactory->create();
        $cmOrderId = $this->request->getParam('id');
        if (!$cmOrderId) {
            $resultPage->setHttpResponseCode(404);

            return $resultPage;
        }

        try {
            /** @var CMOrderInterface $cmOrder */
            $cmOrder = $this->cmOrderRepository->getByOrderKey($cmOrderId);

            /** @var OrderInterface $order */
            $order = $this->orderRepository->get($cmOrder->getOrderId());

            /** @var Payment $payment */
            $payment = $order->getPayment();

            $cmOrderDetails = $this->orderService->get($cmOrder);

            if (!$cmOrderDetails) {
                throw new NoSuchEntityException(
                    __('CM Order with ID "%1" does not exist.', $cmOrderId)
                );
            }

            if (!empty($cmOrderDetails['considered_safe'])) {
                $payment->setNotificationResult(true);
                $payment->accept(false);
                $this->orderService->setOrderStatus($order, $payment->getMethod());
            }
        } catch (NoSuchEntityException $e) {
            $resultPage->setHttpResponseCode(404);

            return $resultPage;
        }

        $resultPage->setHttpResponseCode(200);
        $resultPage->setData([]);

        return $resultPage;
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
