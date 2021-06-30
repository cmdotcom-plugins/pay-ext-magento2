<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Plugin\Checkout\Model;

use CM\Payments\Api\Model\Data\IssuerInterface;
use CM\Payments\Api\Model\Data\IssuerInterfaceFactory;
use CM\Payments\Api\Model\Data\PaymentMethodAdditionalDataInterface;
use CM\Payments\Api\Model\Data\PaymentMethodAdditionalDataInterfaceFactory;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Config\Config as ConfigService;
use CM\Payments\Model\ConfigProvider;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionInterfaceFactory;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class PaymentInformationManagement
{
    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var MethodServiceInterface
     */
    private $methodService;

    /**
     * @var PaymentMethodAdditionalDataInterfaceFactory
     */
    private $paymentMethodAdditionalDataFactory;

    /**
     * @var IssuerInterfaceFactory
     */
    private $issuerFactory;

    /**
     * @var PaymentDetailsExtensionInterfaceFactory
     */
    private $paymentDetailsExtensionFactory;

    /**
     * PaymentInformationManagement constructor
     *
     * @param ConfigService $configService
     * @param CartRepositoryInterface $quoteRepository
     * @param MethodServiceInterface $methodService
     * @param PaymentMethodAdditionalDataInterfaceFactory $paymentMethodAdditionalDataFactory
     * @param IssuerInterfaceFactory $issuerFactory
     * @param PaymentDetailsExtensionInterfaceFactory $paymentDetailsExtensionFactory
     */
    public function __construct(
        ConfigService $configService,
        CartRepositoryInterface $quoteRepository,
        MethodServiceInterface $methodService,
        PaymentMethodAdditionalDataInterfaceFactory $paymentMethodAdditionalDataFactory,
        IssuerInterfaceFactory $issuerFactory,
        PaymentDetailsExtensionInterfaceFactory $paymentDetailsExtensionFactory
    ) {
        $this->configService = $configService;
        $this->quoteRepository = $quoteRepository;
        $this->methodService = $methodService;
        $this->paymentMethodAdditionalDataFactory = $paymentMethodAdditionalDataFactory;
        $this->issuerFactory = $issuerFactory;
        $this->paymentDetailsExtensionFactory = $paymentDetailsExtensionFactory;
    }

    /**
     * @param PaymentInformationManagementInterface $subject
     * @param PaymentDetailsInterface $paymentDetails
     * @param int $cartId
     * @return PaymentDetailsInterface
     * @throws NoSuchEntityException
     */
    public function afterGetPaymentInformation(
        PaymentInformationManagementInterface $subject,
        PaymentDetailsInterface $paymentDetails,
        $cartId
    ): PaymentDetailsInterface {
        if ($this->configService->isEnabled()) {
            $quote = $this->quoteRepository->getActive($cartId);

            $availablePaymentMethods = $paymentDetails->getPaymentMethods();
            $availableProfileMethods = $this->methodService->getAvailablePaymentMethods($quote);
            $issuers = [];

            $paymentDetailsExtension = $paymentDetails->getExtensionAttributes();
            if ($paymentDetailsExtension == null) {
                /** @var PaymentDetailsExtensionInterface $paymentDetailsExtension */
                $paymentDetailsExtension = $this->paymentDetailsExtensionFactory->create();
            }

            foreach ($availablePaymentMethods as $id => $paymentMethod) {
                if (strpos($paymentMethod->getCode(), ConfigProvider::CODE . '_') === false) {
                    continue;
                }

                if (!isset($availableProfileMethods[$paymentMethod->getCode()])) {
                    unset($availablePaymentMethods[$id]);
                }

                if ($paymentMethod->getCode() == ConfigProvider::CODE_IDEAL) {
                    if (isset($availableProfileMethods[$paymentMethod->getCode()])) {
                        $methodData = $availableProfileMethods[$paymentMethod->getCode()];
                        if (!empty($methodData['ideal_details']['issuers'])) {
                            foreach ($methodData['ideal_details']['issuers'] as $issuer) {
                                /** @var IssuerInterface $issuerObject */
                                $issuerObject = $this->issuerFactory->create();
                                $issuerObject->addData($issuer);
                                $issuers[] = $issuerObject;
                            }
                        }
                    }

                    /** @var PaymentMethodAdditionalDataInterface $paymentMethodAdditionalData */
                    $paymentMethodAdditionalData = $this->paymentMethodAdditionalDataFactory->create();
                    $paymentMethodAdditionalData->setIssuers($issuers);
                    $paymentDetailsExtension->setData($paymentMethod->getCode(), $paymentMethodAdditionalData);
                }
            }

            $paymentDetails->setExtensionAttributes($paymentDetailsExtension);
            $paymentDetails->setPaymentMethods($availablePaymentMethods);
        }

        return $paymentDetails;
    }
}
