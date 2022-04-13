<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Client\Api\OrderInterface as OrderClientInterface;
use CM\Payments\Client\Model\Response\PaymentMethod;
use CM\Payments\Client\Request\OrderGetMethodsRequestFactory;
use CM\Payments\Exception\PaymentMethodNotFoundException;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Model\ConfigProvider;
use Exception;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class MethodService implements MethodServiceInterface
{
    /**
     * @var ConfigInterface
     */
    private $configService;

    /**
     * @var OrderClientInterface
     */
    private $orderClient;

    /**
     * @var OrderGetMethodsRequestFactory
     */
    private $orderGetMethodsRequestFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var CMPaymentsLogger
     */

    private $logger;

    /**
     * @var OrderServiceInterface
     */

    private $orderService;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * MethodService constructor
     *
     * @param ConfigInterface $configService
     * @param OrderClientInterface $orderClient
     * @param OrderServiceInterface $orderService
     * @param CartRepositoryInterface $quoteRepository,
     * @param OrderGetMethodsRequestFactory $orderGetMethodsRequestFactory
     * @param ManagerInterface $eventManager
     * @param CMPaymentsLogger $cmPaymentsLogger
     */
    public function __construct(
        ConfigInterface $configService,
        OrderClientInterface $orderClient,
        OrderServiceInterface $orderService,
        CartRepositoryInterface $quoteRepository,
        OrderGetMethodsRequestFactory $orderGetMethodsRequestFactory,
        ManagerInterface $eventManager,
        CMPaymentsLogger $cmPaymentsLogger
    ) {
        $this->configService = $configService;
        $this->orderClient = $orderClient;
        $this->quoteRepository = $quoteRepository;
        $this->orderGetMethodsRequestFactory = $orderGetMethodsRequestFactory;
        $this->eventManager = $eventManager;
        $this->logger = $cmPaymentsLogger;
        $this->orderService = $orderService;
    }

    /**
     * @inheritDoc
     */
    public function getMethodsByQuote(CartInterface $quote, array $magentoMethods): array
    {
        if ($quote->getGrandTotal() <= 0) {
            return $magentoMethods;
        }

        try {
            $cmOrderKey = $this->getCmOrderKey($quote);
            $this->saveOrderKey($quote, $cmOrderKey);

            // Needed for Klarna, AfterPay availability
            $this->orderService->createItemsByQuote($quote, $cmOrderKey);

            return $this->filterMethods($magentoMethods, $this->getCmMethods($cmOrderKey));
        } catch (Exception $e) {
            $this->logger->error(
                'CM Get Available Methods request',
                [
                    'error' => $e->getMessage(),
                ]
            );
            // Remove cm_payments_ideal if available because of missing issuer list.
            // Remove cm_payments_klarna if we have exception (can be shopper creation problem or other).
            return array_filter($magentoMethods, function ($method) {
                return !in_array($method->getCode(), [ConfigProvider::CODE_IDEAL, ConfigProvider::CODE_KLARNA]);
            });
        }
    }

    /**
     * @inheritDoc
     */
    public function getCmMethods(string $orderKey): array
    {
        return $this->orderClient->getMethods(
            $orderKey
        );
    }

    /**
     * @inheritDoc
     */
    public function filterMethods(array $magentoMethods, array $cmMethods): array
    {
        $mappedMethods = $this->getMappedCmPaymentMethods($cmMethods);
        foreach ($magentoMethods as $key => $method) {
            if ($this->isCmPaymentsMethod($method->getCode()) &&
                (empty($mappedMethods[$method->getCode()]) && $method->getCode() !== MethodServiceInterface::CM_METHOD_MENU)
            ) {
                unset($magentoMethods[$key]);
            }
        }
        return $magentoMethods;
    }

    /**
     * @inheritDoc
     */
    public function getMethodFromList(string $method, array $cmMethods): PaymentMethod
    {
        foreach ($cmMethods as $cmMethod) {
            if ($cmMethod->getMethod() === $method) {
                return $cmMethod;
            }
        }

        throw new PaymentMethodNotFoundException(__('Method not found'));
    }

    /**
     * @inheritDoc
     */
    public function isCmPaymentsMethod(string $paymentMethodCode): bool
    {
        return strpos($paymentMethodCode, ConfigProvider::CODE) !== false;
    }

    /**
     * @param CartInterface $quote
     * @return string
     * @throws LocalizedException
     */
    private function getCmOrderKey(CartInterface $quote): string
    {
        $cmOrder = $this->orderService->createByQuote($quote);

        if (empty($cmOrder->getOrderKey())) {
            throw new LocalizedException(
                __("The Methods were not requested properly because of CM Order creation problem.")
            );
        }

        return $cmOrder->getOrderKey();
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
            $mappedMethodCode = $this->getMappedMethod($cmPaymentMethod->getMethod());
            if (empty($mappedMethodCode)) {
                continue;
            }

            if ($this->configService->isPaymentMethodActive($mappedMethodCode)) {
                $methods[$mappedMethodCode] = $cmPaymentMethod;
            }
        }

        return $methods;
    }

    /**
     * @param CartInterface $quote
     * @param string $cmOrderKey
     */
    private function saveOrderKey(CartInterface $quote, string $cmOrderKey): void
    {
        $quote->setData('cm_order_key', $cmOrderKey);
        $this->quoteRepository->save($quote);
    }

    /**
     * @param string $method
     * @return string
     */
    private function getMappedMethod(string $method): string
    {
        foreach (array_keys(self::METHODS_MAPPING) as $key) {
            preg_match('/'. $key .'/', $method, $matches);
            if (count($matches) > 0) {
                return self::METHODS_MAPPING[$key];
            }
        }

        return '';
    }
}
