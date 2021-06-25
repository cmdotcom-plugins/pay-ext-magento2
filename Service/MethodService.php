<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Client\ApiClientInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Client\Model\CMPaymentFactory;
use CM\Payments\Client\Model\CMPaymentUrlFactory;
use CM\Payments\Client\Request\OrderGetMethodsRequest;
use CM\Payments\Client\Request\OrderGetMethodsRequestFactory;
use CM\Payments\Config\Config as ConfigService;
use CM\Payments\Logger\CMPaymentsLogger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;

class MethodService implements MethodServiceInterface
{
    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var ApiClientInterface
     */
    private $apiClient;

    /**
     * @var OrderRequestBuilderInterface
     */
    private $orderRequestBuilder;

    /**
     * @var OrderGetMethodsRequestFactory
     */
    private $orderGetMethodsRequestFactory;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * MethodService constructor
     *
     * @param ConfigService $configService
     * @param ApiClientInterface $apiClient
     * @param OrderRequestBuilderInterface $orderRequestBuilder
     * @param OrderGetMethodsRequestFactory $orderGetMethodsRequestFactory
     * @param CMPaymentsLogger $cmPaymentsLogger
     */
    public function __construct(
        ConfigService $configService,
        ApiClientInterface $apiClient,
        OrderRequestBuilderInterface $orderRequestBuilder,
        OrderGetMethodsRequestFactory $orderGetMethodsRequestFactory,
        CMPaymentsLogger $cmPaymentsLogger
    ) {
        $this->configService = $configService;
        $this->apiClient = $apiClient;
        $this->orderRequestBuilder = $orderRequestBuilder;
        $this->orderGetMethodsRequestFactory = $orderGetMethodsRequestFactory;
        $this->logger = $cmPaymentsLogger;
    }

    /**
     * @inheritDoc
     */
    public function getAvailablePaymentMethods(CartInterface $quote): array
    {
        $availableMethods = [];
        try {
            $orderCreateRequest = $this->orderRequestBuilder->createByQuote($quote, true);
            $response = $this->apiClient->execute(
                $orderCreateRequest
            );

            if (!empty($response['order_key'])) {
                $quote->setData('cm_order_key', $response['order_key']);

                /** @var OrderGetMethodsRequest $orderGetMethodsRequest */
                $orderGetMethodsRequest = $this->orderGetMethodsRequestFactory->create(
                    [
                        'orderKey' => $response['order_key']
                    ]
                );

                $availableProfileMethods = $this->apiClient->execute(
                    $orderGetMethodsRequest
                );

                foreach ($availableProfileMethods as $availableProfileMethod) {
                    $availableProfileMethodCode = $availableProfileMethod['method'];

                    if (!isset(self::METHODS_MAPPING[$availableProfileMethodCode])) {
                        continue;
                    }

                    $mappedMethodCode = self::METHODS_MAPPING[$availableProfileMethodCode];
                    if ($this->configService->isPaymentMethodActive($mappedMethodCode)) {
                        $methodData = [];
                        if (isset($availableProfileMethod['ideal_details'])) {
                            $methodData['ideal_details']['issuers']
                                = $this->prepareIdealIssuers($availableProfileMethod['ideal_details']['issuers']);
                        }

                        $availableMethods[$mappedMethodCode] = $methodData;
                    }
                }
            } else {
                throw new LocalizedException(
                    __("The Methods were not requested properly because of CM Order creation problem.")
                );
            }
        } catch (LocalizedException $e) {
            $this->logger->info(
                'CM Get Available Methods request',
                [
                    'error' => $e->getMessage(),
                ]
            );
        }

        return $availableMethods;
    }

    /**
     * @param array $issuerList
     * @return array
     */
    private function prepareIdealIssuers(array $issuerList): array
    {
        $issuers = $resultIssuerList = [];
        foreach ($issuerList as $issuer) {
            $issuers[$issuer['id']] = $issuer['name'];
        }
        asort($issuers, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($issuers as $id => $name) {
            $resultIssuerList[] = ['id' => $id, 'name' => $name];
        }

        return $resultIssuerList;
    }
}
