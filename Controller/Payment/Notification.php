<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Payment;

use CM\Payments\Api\Service\OrderTransactionServiceInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var OrderTransactionServiceInterface
     */
    private $orderTransactionService;

    /**
     * Notification constructor.
     *
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param OrderTransactionServiceInterface $orderTransactionService
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        OrderTransactionServiceInterface $orderTransactionService,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->orderTransactionService = $orderTransactionService;
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
            $this->orderTransactionService->process($cmOrderId);

            $resultPage->setHttpResponseCode(200);
            $resultPage->setData([]);

            return $resultPage;
        } catch (NoSuchEntityException $e) {
            $resultPage->setHttpResponseCode(404);

            return $resultPage;
        }
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
