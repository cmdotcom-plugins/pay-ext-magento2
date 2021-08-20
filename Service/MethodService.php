<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Data\PaymentMethodAdditionalDataInterfaceFactory;
use CM\Payments\Api\Service\Method\ExtendMethodInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\OrderItemsRequestBuilderInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Client\Api\OrderInterface as OrderClientInterface;
use CM\Payments\Client\Model\Response\OrderCreate;
use CM\Payments\Client\Model\Response\PaymentMethod;
use CM\Payments\Client\Request\OrderGetMethodsRequestFactory;
use CM\Payments\Config\Config as ConfigService;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Model\ConfigProvider;
use Exception;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionInterfaceFactory;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;

class MethodService implements MethodServiceInterface
{
    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var OrderClientInterface
     */
    private $orderClient;

    /**
     * @var OrderRequestBuilderInterface
     */
    private $orderRequestBuilder;

    /**
     * @var OrderItemsRequestBuilderInterface
     */
    private $orderItemsRequestBuilder;

    /**
     * @var OrderGetMethodsRequestFactory
     */
    private $orderGetMethodsRequestFactory;

    /**
     * @var PaymentMethodAdditionalDataInterfaceFactory
     */
    private $paymentMethodAdditionalDataFactory;

    /**
     * @var ExtendMethodInterface[]
     */
    private $methods;

    /**
     * @var PaymentDetailsExtensionInterfaceFactory
     */
    private $paymentDetailsExtensionFactory;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * MethodService constructor
     *
     * @param ConfigService $configService
     * @param OrderClientInterface $orderClient
     * @param OrderRequestBuilderInterface $orderRequestBuilder
     * @param OrderItemsRequestBuilderInterface $orderItemsRequestBuilder
     * @param OrderGetMethodsRequestFactory $orderGetMethodsRequestFactory
     * @param PaymentMethodAdditionalDataInterfaceFactory $paymentMethodAdditionalDataFactory
     * @param ExtendMethodInterface[] $methods
     * @param PaymentDetailsExtensionInterfaceFactory $paymentDetailsExtensionFactory
     * @param CMPaymentsLogger $cmPaymentsLogger
     */
    public function __construct(
        ConfigService $configService,
        OrderClientInterface $orderClient,
        OrderRequestBuilderInterface $orderRequestBuilder,
        OrderItemsRequestBuilderInterface $orderItemsRequestBuilder,
        OrderGetMethodsRequestFactory $orderGetMethodsRequestFactory,
        PaymentMethodAdditionalDataInterfaceFactory $paymentMethodAdditionalDataFactory,
        array $methods,
        PaymentDetailsExtensionInterfaceFactory $paymentDetailsExtensionFactory,
        CMPaymentsLogger $cmPaymentsLogger
    ) {
        $this->configService = $configService;
        $this->orderClient = $orderClient;
        $this->orderRequestBuilder = $orderRequestBuilder;
        $this->orderItemsRequestBuilder = $orderItemsRequestBuilder;
        $this->orderGetMethodsRequestFactory = $orderGetMethodsRequestFactory;
        $this->paymentMethodAdditionalDataFactory = $paymentMethodAdditionalDataFactory;
        $this->methods = $methods;
        $this->paymentDetailsExtensionFactory = $paymentDetailsExtensionFactory;
        $this->logger = $cmPaymentsLogger;
    }

    /**
     * @inheritDoc
     */
    public function addMethodAdditionalData(
        CartInterface $quote,
        PaymentDetailsInterface $paymentDetails
    ): PaymentDetailsInterface {
        if ($quote->getGrandTotal() <= 0) {
            return $paymentDetails;
        }

        $availablePaymentMethods = $paymentDetails->getPaymentMethods();
        try {
            $cmOrder = $this->createCmOrder($quote);
            if (empty($cmOrder->getOrderKey())) {
                throw new LocalizedException(
                    __("The Methods were not requested properly because of CM Order creation problem.")
                );
            } else {
                // Needed for Klarna availability
                $this->createCmOrderItems($quote, $cmOrder->getOrderKey());
            }

            $cmPaymentMethods = $this->orderClient->getMethods(
                $cmOrder->getOrderKey()
            );
            $cmPaymentMethods = $this->getMappedCmPaymentMethods($cmPaymentMethods);

            $paymentDetailsExtension = $paymentDetails->getExtensionAttributes();
            if ($paymentDetailsExtension == null) {
                /** @var PaymentDetailsExtensionInterface $paymentDetailsExtension */
                $paymentDetailsExtension = $this->paymentDetailsExtensionFactory->create();
            }

            foreach ($availablePaymentMethods as $id => $paymentMethod) {
                if (!$this->isCmPaymentsMethod($paymentMethod->getCode())) {
                    continue;
                }

                if (!isset($cmPaymentMethods[$paymentMethod->getCode()])) {
                    unset($availablePaymentMethods[$id]);
                }

                foreach ($this->methods as $method) {
                    if (isset($cmPaymentMethods[$paymentMethod->getCode()])) {
                        $paymentDetailsExtension = $method->extend(
                            $paymentMethod->getCode(),
                            $cmPaymentMethods[$paymentMethod->getCode()],
                            $paymentDetailsExtension
                        );
                    }
                }
            }

            $paymentDetails->setExtensionAttributes($paymentDetailsExtension);
            $paymentDetails->setPaymentMethods($availablePaymentMethods);
        } catch (Exception $e) {
            $this->logger->error(
                'CM Get Available Methods request',
                [
                    'error' => $e->getMessage(),
                ]
            );

            // Remove cm_payments_ideal if available because of missing issuer list.
            $availablePaymentMethods = array_filter($availablePaymentMethods, function ($method) {
                return $method->getCode() !== ConfigProvider::CODE_IDEAL;
            });

            $paymentDetails->setPaymentMethods($availablePaymentMethods);
        }

        return $paymentDetails;
    }

    /**
     * @param CartInterface $quote
     * @return OrderCreate
     * @throws LocalizedException
     */
    private function createCmOrder(CartInterface $quote): OrderCreate
    {
        $orderCreateRequest = $this->orderRequestBuilder->createByQuote($quote);

        return $this->orderClient->create(
            $orderCreateRequest
        );
    }

    /**
     * @param CartInterface $quote
     * @param string $orderKey
     * @return void
     * @throws LocalizedException
     */
    private function createCmOrderItems(CartInterface $quote, string $orderKey): void
    {
        $orderCreateItemsRequest = $this->orderItemsRequestBuilder->createByQuoteItems(
            $orderKey,
            $quote->getAllVisibleItems()
        );

        $this->orderClient->createItems($orderCreateItemsRequest);
    }

    /**
     * @param PaymentMethod[] $cmPaymentMethods
     * @return array<string, PaymentMethod>
     *
     * @throws NoSuchEntityException
     */
    private function getMappedCmPaymentMethods(array $cmPaymentMethods): array
    {
        $methods = [];
        foreach ($cmPaymentMethods as $cmPaymentMethod) {
            if (!isset(self::METHODS_MAPPING[$cmPaymentMethod->getMethod()])) {
                continue;
            }

            $mappedMethodCode = self::METHODS_MAPPING[$cmPaymentMethod->getMethod()];
            if ($this->configService->isPaymentMethodActive($mappedMethodCode)) {
                $methods[$mappedMethodCode] = $cmPaymentMethod;
            }
        }

        return $methods;
    }

    /**
     * @param string $paymentMethodCode
     * @return bool
     */
    private function isCmPaymentsMethod(string $paymentMethodCode): bool
    {
        return strpos($paymentMethodCode, ConfigProvider::CODE . '_') !== false;
    }
}
