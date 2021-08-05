<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Payment;

use CM\Payments\Api\Service\OrderTransactionServiceInterface;
use CM\Payments\Logger\CMPaymentsLogger;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Notification extends Action implements HttpGetActionInterface, CsrfAwareActionInterface
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
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * @var OrderTransactionServiceInterface
     */
    private $orderTransactionService;

    /**
     * Notification constructor.
     *
     * @param Context $context
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param OrderTransactionServiceInterface $orderTransactionService
     * @param CMPaymentsLogger $logger
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        OrderTransactionServiceInterface $orderTransactionService,
        CMPaymentsLogger $logger
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->orderTransactionService = $orderTransactionService;

        parent::__construct($context);
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
            $resultPage->setData(['message' => __('No such entity')]);
            $resultPage->setHttpResponseCode(400);

            return $resultPage;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            $resultPage->setHttpResponseCode(500);
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
