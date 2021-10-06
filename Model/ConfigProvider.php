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
use CM\Payments\Model\Adminhtml\Source\Cctype as CcTypeSource;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Asset\Source as AssetSource;

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
    public const CODE_MAESTRO = 'cm_payments_maestro';
    public const CODE_VPAY = 'cm_payments_vpay';
    public const CODE_IDEAL = 'cm_payments_ideal';
    public const CODE_PAYPAL = 'cm_payments_paypal';
    public const CODE_BANCONTACT = 'cm_payments_bancontact';
    public const CODE_ELV = 'cm_payments_elv';
    public const CODE_KLARNA = 'cm_payments_klarna';
    public const CODE_CM_PAYMENTS_MENU = 'cm_payments';

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var AssetSource
     */
    private $assetSource;

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var CcTypeSource
     */
    private $ccTypeSource;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * ConfigProvider constructor
     *
     * @param AssetRepository $assetRepository
     * @param AssetSource $assetSource
     * @param ConfigService $configService
     * @param CcTypeSource $ccTypeSource
     * @param CMPaymentsLogger $cmPaymentsLogger
     */
    public function __construct(
        AssetRepository $assetRepository,
        AssetSource $assetSource,
        ConfigService $configService,
        CcTypeSource $ccTypeSource,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        CMPaymentsLogger $cmPaymentsLogger
    ) {
        $this->assetRepository = $assetRepository;
        $this->assetSource = $assetSource;
        $this->configService = $configService;
        $this->ccTypeSource = $ccTypeSource;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
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
                        'image'      => $this->getImage($this->getCode()),
                    ],
                ],
            ];

            foreach (MethodServiceInterface::METHODS as $code) {
                $config['payment'][$code]['image'] = $this->getImage($code);

                if ($code == self::CODE_IDEAL) {
                    $config['payment'][$code]['issuers'] = [];
                }

                if (in_array($code, [self::CODE_CREDIT_CARD, self::CODE_MAESTRO, self::CODE_VPAY])) {
                    $config['payment'][$code]['encryption_library'] = $this->configService->getEncryptLibrary();
                    $config['payment'][$code]['nsa3ds_library'] = $this->configService->getNsa3dsLibrary();
                    $config['payment'][$code]['allowedTypes'] = $this->getCreditCardAllowedTypes($code);
                }

                if ($code == self::CODE_CREDIT_CARD) {
                    $config['payment'][$code]['is_direct'] = $this->configService->isCreditCardDirect();
                    $config['payment'][$code]['allowedTypesIcons'] = $this->getCreditCardAllowedTypesIcons($code);
                    $config['payment'][$code]['successPage'] = $this->urlBuilder->getUrl(
                        'checkout/onepage/success',
                        ['_secure' => $this->request->isSecure()]
                    );

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

    /**
     * Retrieve allowed credit card types
     *
     * @param string $code
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCreditCardAllowedTypes(string $code): array
    {
        $availableTypes = [];
        switch ($code) {
            case self::CODE_CREDIT_CARD:
                $allowedTypes = explode(',', $this->configService->getCreditCardAllowedTypes());
                $availableTypes = $this->ccTypeSource->toOptionArray();
                foreach ($availableTypes as $key => $type) {
                    if (!in_array($type['value'], $allowedTypes)) {
                        unset($availableTypes[$key]);
                    }
                }

                break;
            case self::CODE_MAESTRO:
                $availableTypes = [
                    ['value' => 'MD', 'label' => 'Maestro Domestic'],
                    ['value' => 'MI', 'label' => 'Maestro International'],
                ];

                break;
            case self::CODE_VPAY:
                $availableTypes = [
                    ['value' => 'VP', 'label' => 'V-Pay'],
                ];

                break;
        }

        return $availableTypes;
    }

    /**
     * Get icons for allowed credit card types
     *
     * @param string $code
     * @return array
     * @throws NoSuchEntityException|LocalizedException
     */
    public function getCreditCardAllowedTypesIcons(string $code): array
    {
        $types = $this->getCreditCardAllowedTypes($code);
        $icons = [];
        foreach ($types as $type) {
            $asset = $this->assetRepository->createAsset('CM_Payments::images/creditcard/' .
                strtolower($type['value']) . '.svg');
            $placeholder = $this->assetSource->findSource($asset);
            if ($placeholder) {
                $icons[] = [
                    'url'    => $asset->getUrl(),
                    'width'  => 60,
                    'height' => 60,
                    'title'  => __($type['label']),
                ];
            }
        }

        return $icons;
    }
}
