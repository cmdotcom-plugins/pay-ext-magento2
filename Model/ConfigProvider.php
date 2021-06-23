<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model;

use CM\Payments\Api\Client\ApiClientInterface;
use CM\Payments\Api\Service\OrderRequestBuilderInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Config\Config as ConfigService;
use Exception;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * General Method code
     */
    public const CODE = 'cm_payments';

    /**
     * Available Methods Codes
     */
    public const CODE_CREDIT_CARD = 'cm_payments_creditcard';
    public const CODE_IDEAL = 'cm_payments_ideal';
    public const CODE_PAYPAL = 'cm_payments_paypal';

    /**
     * Mapping of CM methods to magento
     */
    public const METHODS_MAPPING = [
        'VISA' => self::CODE_CREDIT_CARD,
        'MASTERCARD' => self::CODE_CREDIT_CARD,
        'MAESTRO' => self::CODE_CREDIT_CARD,
        'IDEAL' => self::CODE_IDEAL,
        'PAYPAL_EXPRESS_CHECKOUT' => self::CODE_PAYPAL
    ];

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var OrderRequestBuilderInterface
     */
    private $orderRequestBuilder;

    /**
     * @var ApiClientInterface
     */
    private $apiClient;

    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var array
     */
    private $availableMethods = [];

    /**
     * ConfigProvider constructor
     *
     * @param AssetRepository $assetRepository
     * @param CheckoutSession $checkoutSession
     * @param OrderRequestBuilderInterface $orderRequestBuilder
     * @param ApiClientInterface $apiClient ,
     * @param OrderServiceInterface $orderService
     * @param CartRepositoryInterface $quoteRepository
     * @param ConfigService $configService
     */
    public function __construct(
        AssetRepository $assetRepository,
        CheckoutSession $checkoutSession,
        OrderRequestBuilderInterface $orderRequestBuilder,
        ApiClientInterface $apiClient,
        OrderServiceInterface $orderService,
        CartRepositoryInterface $quoteRepository,
        ConfigService $configService
    ) {
        $this->assetRepository = $assetRepository;
        $this->checkoutSession = $checkoutSession;
        $this->orderRequestBuilder = $orderRequestBuilder;
        $this->apiClient = $apiClient;
        $this->orderService = $orderService;
        $this->quoteRepository = $quoteRepository;
        $this->configService = $configService;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        $config = [
            'payment' => [
                $this->getCode() => [
                    'image' => $this->getImage($this->getCode()),
                ],
            ],
        ];

        $availableMethods = $this->getAvailableMethods();
        foreach ($availableMethods as $availableMethod) {
            $availableMethodName = $availableMethod['method'];

            if (!isset(self::METHODS_MAPPING[$availableMethodName])) {
                continue;
            }

            $mappedMethodName = self::METHODS_MAPPING[$availableMethodName];
            try {
                if ($this->configService->isPaymentMethodActive($mappedMethodName)) {
                    $config['payment'][$mappedMethodName]['image'] = $this->getImage($mappedMethodName);

                    if (isset($availableMethod['ideal_details'])) {
                        $config['payment'][$mappedMethodName]['issuers']
                            = $availableMethod['ideal_details']['issuers'];
                    }
                }
            } catch (Exception $e) {
                continue;
            }
        }

        return $config;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return static::CODE;
    }

    /**
     * @param string $code
     * @return string
     */
    public function getImage(string $code): string
    {
        return $this->assetRepository->getUrl('CM_Payments::images/methods/' . $code . '.svg');
    }

    /**
     * Get available CM payments methods
     *
     * @return array
     */
    private function getAvailableMethods(): array
    {
        if (!$this->availableMethods) {
            try {
                /** @var Quote $quote */
                $quote = $this->checkoutSession->getQuote();
                if (!$quote->getData('cm_order_key')) {
                    $orderCreateRequest = $this->orderRequestBuilder->createByQuote($quote, true);
                    $response = $this->apiClient->execute(
                        $orderCreateRequest
                    );

                    $quote->setData('cm_order_key', $response['order_key']);
                    $this->quoteRepository->save($quote);
                }

                if ($quote->getData('cm_order_key')) {
                    $this->availableMethods = $this->orderService->getAvailablePaymentMethods(
                        $quote->getData('cm_order_key')
                    );
                }
            } catch (Exception $e) {
                $this->availableMethods = [];
            }
        }

        return $this->availableMethods;
    }
}
