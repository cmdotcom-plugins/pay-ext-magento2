<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model;

use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Config\Config as ConfigService;
use CM\Payments\Logger\CMPaymentsLogger;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Quote\Api\Data\CartInterface;

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
    public const CODE_BANCONTACT = 'cm_payments_bancontact';

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var MethodServiceInterface
     */
    private $methodService;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * ConfigProvider constructor
     *
     * @param AssetRepository $assetRepository
     * @param CheckoutSession $checkoutSession
     * @param ConfigService $configService
     * @param MethodServiceInterface $methodService
     * @param CMPaymentsLogger $cmPaymentsLogger
     */
    public function __construct(
        AssetRepository $assetRepository,
        CheckoutSession $checkoutSession,
        ConfigService $configService,
        MethodServiceInterface $methodService,
        CMPaymentsLogger $cmPaymentsLogger
    ) {
        $this->assetRepository = $assetRepository;
        $this->checkoutSession = $checkoutSession;
        $this->configService = $configService;
        $this->methodService = $methodService;
        $this->logger = $cmPaymentsLogger;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        try {
            $config = [
                'payment' => [
                    $this->getCode() => [
                        'is_enabled' => $this->configService->isEnabled(),
                        'image' => $this->getImage($this->getCode()),
                    ],
                ],
            ];

            /** @var CartInterface $quote */
            $quote = $this->checkoutSession->getQuote();
            $availableProfileMethods = $this->methodService->getAvailablePaymentMethods($quote);
            foreach ($availableProfileMethods as $code => $availableProfileMethod) {
                $config['payment'][$code]['image'] = $this->getImage($code);

                if (isset($availableProfileMethod['ideal_details'])) {
                    $config['payment'][$code]['issuers']
                        = $availableProfileMethod['ideal_details']['issuers'];
                }
            }

            // TODO: Remove this when issue with available methods list (CM side) will be solved
            if (!isset($availableProfileMethod[self::CODE_IDEAL])) {
                $config['payment'][self::CODE_IDEAL]['image'] = $this->getImage(self::CODE_IDEAL);
            }

            // TODO: Remove this when issue with available methods list (CM side) will be solved
            if (!isset($availableProfileMethod[self::CODE_BANCONTACT])) {
                $config['payment'][self::CODE_BANCONTACT]['image'] = $this->getImage(self::CODE_BANCONTACT);
            }
        } catch (LocalizedException $e) {
            $config = [];
            $this->logger->info(
                'CM Get Config for Available Methods request',
                [
                    'error' => $e->getMessage(),
                ]
            );
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
}
