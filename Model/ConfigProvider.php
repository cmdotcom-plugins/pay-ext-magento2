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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\Repository as AssetRepository;

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
    public const CODE_KLARNA = 'cm_payments_klarna';
    public const CODE_CM_PAYMENTS_MENU = 'cm_payments';

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * ConfigProvider constructor
     *
     * @param AssetRepository $assetRepository
     * @param ConfigService $configService
     * @param CMPaymentsLogger $cmPaymentsLogger
     */
    public function __construct(
        AssetRepository $assetRepository,
        ConfigService $configService,
        CMPaymentsLogger $cmPaymentsLogger
    ) {
        $this->assetRepository = $assetRepository;
        $this->configService = $configService;
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

            foreach (MethodServiceInterface::METHODS as $code) {
                $config['payment'][$code]['image'] = $this->getImage($code);

                if ($code == self::CODE_IDEAL) {
                    $config['payment'][$code]['issuers'] = [];
                }
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
