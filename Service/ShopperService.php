<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Service\ShopperRequestBuilderInterface;
use CM\Payments\Api\Service\ShopperServiceInterface;
use CM\Payments\Client\Api\ShopperInterface as ShopperClientInterface;
use CM\Payments\Client\Model\Response\ShopperCreate;
use CM\Payments\Logger\CMPaymentsLogger;
use GuzzleHttp\Exception\RequestException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;

class ShopperService implements ShopperServiceInterface
{
    /**
     * @var ShopperClientInterface
     */
    private $shopperClient;

    /**
     * @var ShopperRequestBuilderInterface
     */
    private $shopperRequestBuilder;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * ShopperService constructor
     *
     * @param ShopperClientInterface $shopperClient
     * @param ShopperRequestBuilderInterface $shopperRequestBuilder
     * @param ManagerInterface $eventManager
     * @param CMPaymentsLogger $cmPaymentsLogger
     */
    public function __construct(
        ShopperClientInterface $shopperClient,
        ShopperRequestBuilderInterface $shopperRequestBuilder,
        ManagerInterface $eventManager,
        CMPaymentsLogger $cmPaymentsLogger
    ) {
        $this->shopperClient = $shopperClient;
        $this->shopperRequestBuilder = $shopperRequestBuilder;
        $this->eventManager = $eventManager;
        $this->logger = $cmPaymentsLogger;
    }

    /**
     * @inheritDoc
     */
    public function createByQuoteAddress(
        AddressInterface $quoteAddress
    ): ShopperCreate {
        $shopperCreateRequest = $this->shopperRequestBuilder->createByQuoteAddress($quoteAddress);

        $this->logger->info(
            'CM Create shopper request (By Quote Address)',
            [
                'quoteAddressId' => $quoteAddress->getId(),
                'requestPayload' => $shopperCreateRequest->getPayload()
            ]
        );

        $this->eventManager->dispatch('cmpayments_before_shopper_create_by_quote_address', [
            'address' => $quoteAddress,
            'shopperCreateRequest' => $shopperCreateRequest,
        ]);

        try {
            $shopperCreateResponse = $this->shopperClient->create(
                $shopperCreateRequest
            );

            $this->eventManager->dispatch('cmpayments_after_shopper_create_by_quote_address', [
                'address' => $quoteAddress,
                'shopperCreateResponse' => $shopperCreateResponse,
            ]);
        } catch (RequestException $exception) {
            $this->logger->info(
                'CM Create shopper request error (By Quote Address)',
                [
                    'quoteAddressId' => $quoteAddress->getId(),
                    'exceptionMessage' => $exception->getMessage()
                ]
            );

            throw new LocalizedException(
                __('The shopper by quote address with ID "%1" was not created properly.', $quoteAddress->getId())
            );
        }

        return $shopperCreateResponse;
    }

    /**
     * @inheritDoc
     */
    public function createByOrderAddress(
        OrderAddressInterface $orderAddress
    ): ShopperCreate {
        $shopperCreateRequest = $this->shopperRequestBuilder->createByOrderAddress($orderAddress);

        $this->logger->info(
            'CM Create shopper request (By Order Address)',
            [
                'quoteAddressId' => $orderAddress->getEntityId(),
                'requestPayload' => $shopperCreateRequest->getPayload()
            ]
        );

        $this->eventManager->dispatch('cmpayments_before_shopper_create_by_order_address', [
            'address' => $orderAddress,
            'shopperCreateRequest' => $shopperCreateRequest,
        ]);

        try {
            $shopperCreateResponse = $this->shopperClient->create(
                $shopperCreateRequest
            );

            $this->eventManager->dispatch('cmpayments_after_shopper_create_by_order_address', [
                'address' => $orderAddress,
                'shopperCreateResponse' => $shopperCreateResponse,
            ]);
        } catch (RequestException $exception) {
            $this->logger->info(
                'CM Create shopper request error (By Order Address)',
                [
                    'orderAddressId' => $orderAddress->getEntityId(),
                    'exceptionMessage' => $exception->getMessage()
                ]
            );

            throw new LocalizedException(
                __('The shopper by order address with ID "%1" was not created properly.', $orderAddress->getEntityId())
            );
        }

        return $shopperCreateResponse;
    }
}
