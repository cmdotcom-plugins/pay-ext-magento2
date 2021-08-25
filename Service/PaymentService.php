<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use CM\Payments\Api\Data\CardDetailsInterface;
use CM\Payments\Api\Model\Data\PaymentInterface as CMPaymentDataInterface;
use CM\Payments\Api\Model\Data\PaymentInterfaceFactory as CMPaymentDataFactory;
use CM\Payments\Api\Model\OrderRepositoryInterface as CMOrderRepositoryInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface as CMPaymentRepositoryInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Api\Service\PaymentRequestBuilderInterface;
use CM\Payments\Api\Service\PaymentServiceInterface;
use CM\Payments\Client\Api\CMPaymentInterface;
use CM\Payments\Client\Api\PaymentInterface as CMPaymentClientInterface;
use CM\Payments\Client\Model\CMPaymentFactory;
use CM\Payments\Exception\EmptyPaymentIdException;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Model\ConfigProvider;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface;

class PaymentService implements PaymentServiceInterface
{
    /**
     * @var CMPaymentClientInterface
     */
    private $paymentClient;

    /**
     * @var CMPaymentFactory
     */
    private $cmPaymentFactory;

    /**
     * @var PaymentRequestBuilderInterface
     */
    private $paymentRequestBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CMPaymentRepositoryInterface
     */
    private $cmPaymentRepository;

    /**
     * @var CMPaymentDataFactory
     */
    private $cmPaymentDataFactory;

    /**
     * @var CMOrderRepositoryInterface
     */
    private $cmOrderRepository;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * PaymentService constructor
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param CartRepositoryInterface $cartRepository
     * @param CMPaymentClientInterface $paymentClient
     * @param PaymentRequestBuilderInterface $paymentRequestBuilder
     * @param CMPaymentDataFactory $cmPaymentDataFactory
     * @param OrderServiceInterface $orderService
     * @param CMPaymentFactory $cmPaymentFactory
     * @param CMPaymentRepositoryInterface $cmPaymentRepository
     * @param CMOrderRepositoryInterface $cmOrderRepository
     * @param ManagerInterface $eventManager
     * @param CMPaymentsLogger $logger
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param QuoteManagement $quoteManagement
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository,
        CMPaymentClientInterface $paymentClient,
        PaymentRequestBuilderInterface $paymentRequestBuilder,
        CMPaymentDataFactory $cmPaymentDataFactory,
        OrderServiceInterface $orderService,
        CMPaymentFactory $cmPaymentFactory,
        CMPaymentRepositoryInterface $cmPaymentRepository,
        CMOrderRepositoryInterface $cmOrderRepository,
        ManagerInterface $eventManager,
        CMPaymentsLogger $logger,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        QuoteManagement $quoteManagement
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentClient = $paymentClient;
        $this->paymentRequestBuilder = $paymentRequestBuilder;
        $this->cmPaymentDataFactory = $cmPaymentDataFactory;
        $this->cmPaymentFactory = $cmPaymentFactory;
        $this->cmOrderRepository = $cmOrderRepository;
        $this->cmPaymentRepository = $cmPaymentRepository;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->cartRepository = $cartRepository;
        $this->orderService = $orderService;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->quoteManagement = $quoteManagement;
    }

    /**
     * @inheritDoc
     */
    public function create(string $orderId): CMPaymentInterface
    {
        $order = $this->orderRepository->get($orderId);
        $cmOrder = $this->cmOrderRepository->getByOrderId((int)$order->getEntityId());
        $paymentCreateRequest = $this->paymentRequestBuilder->create(
            $order->getIncrementId(),
            $cmOrder->getOrderKey(),
            $order
        );

        $this->logger->info(
            'CM Create payment request',
            [
                'orderId' => $orderId,
                'requestPayload' => $paymentCreateRequest->getPayload()
            ]
        );

        $this->eventManager->dispatch('cmpayments_before_payment_create', [
            'order' => $order,
            'paymentCreateRequest' => $paymentCreateRequest,
        ]);

        $paymentCreateResponse = null;
        try {
            $paymentCreateResponse = $this->paymentClient->create(
                $paymentCreateRequest
            );
        } catch (GuzzleException $e) {
            $this->logger->info(
                'CM Create payment request error',
                [
                    'orderId' => $orderId,
                    'exceptionMessage' => $e->getMessage()
                ]
            );
        }

        // Cleaning of ELV iban from payment information
        $additionalInformation = $order->getPayment()->getAdditionalInformation();
        if ($order->getPayment()->getMethod() == ConfigProvider::CODE_ELV) {
            unset($additionalInformation['iban']);
            $order->getPayment()->setAdditionalInformation($additionalInformation);
            $this->orderRepository->save($order);
        }

        // Todo: validate and handle response status
        if (!$paymentCreateResponse || !$paymentCreateResponse->getId()) {
            throw new EmptyPaymentIdException(__('Empty payment id'));
        }

        /** @var CMPaymentDataInterface $cmPayment */
        $cmPayment = $this->cmPaymentDataFactory->create();
        $cmPayment->setOrderId((int)$order->getEntityId());
        $cmPayment->setOrderKey($cmOrder->getOrderKey());
        $cmPayment->setIncrementId($order->getIncrementId());
        $cmPayment->setPaymentId($paymentCreateResponse->getId());

        $this->cmPaymentRepository->save($cmPayment);

        $additionalInformation['cm_payment_id'] = $paymentCreateResponse->getId();
        $order->getPayment()->setAdditionalInformation($additionalInformation);
        $this->orderRepository->save($order);

        $cmPayment = $this->cmPaymentFactory->create(
            [
                'id' => $paymentCreateResponse->getId(),
                'status' => $paymentCreateResponse->getStatus(),
                'redirectUrl' => $paymentCreateResponse->getRedirectUrl(),
                'urls' => $paymentCreateResponse->getUrls()
            ]
        );

        $this->eventManager->dispatch('cmpayments_after_payment_create', [
            'order' => $order,
            'cmPayment' => $cmPayment,
        ]);

        return $cmPayment;
    }

    /**
     * @inheritDoc
     */
    public function createByGuestCardDetails(
        string $quoteId,
        CardDetailsInterface $cardDetails,
        BrowserDetailsInterface $browserDetails
    ): CMPaymentInterface {
        $quoteId = (string)$this->maskedQuoteIdToQuoteId->execute($quoteId);

        return $this->createByCardDetails(
            $quoteId,
            $cardDetails,
            $browserDetails
        );
    }

    /**
     * @inheritDoc
     */
    public function createByCardDetails(
        string $quoteId,
        CardDetailsInterface $cardDetails,
        BrowserDetailsInterface $browserDetails
    ): CMPaymentInterface {
        $quote = $this->cartRepository->getActive($quoteId);
        try {
            $quote->reserveOrderId();

            $cmOrder = $this->orderService->createByQuote($quote->getReservedOrderId(), $quote);

            $paymentCreateRequest = $this->paymentRequestBuilder->create(
                $quote->getReservedOrderId(),
                $cmOrder->getOrderKey(),
                null,
                $cardDetails,
                $browserDetails
            );

            $paymentCreateResponse = $this->paymentClient->create(
                $paymentCreateRequest
            );

            return $this->cmPaymentFactory->create(
                [
                    'id' => $paymentCreateResponse->getId(),
                    'status' => $paymentCreateResponse->getStatus(),
                    'redirectUrl' => $paymentCreateResponse->getRedirectUrl(),
                    'urls' => $paymentCreateResponse->getUrls()
                ]
            );
        } catch (Exception $e) {
            $quote->setIsActive(1)->setReservedOrderId(null);
            $this->cartRepository->save($quote);

            throw $e;
        }
    }
}
