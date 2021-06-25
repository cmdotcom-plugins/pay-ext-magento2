<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Plugin\Checkout\Model;

use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Config\Config as ConfigService;
use CM\Payments\Model\ConfigProvider;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class ShippingInformationManagement
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
     * ShippingInformationManagement constructor
     *
     * @param ConfigService $configService
     * @param CartRepositoryInterface $quoteRepository
     * @param MethodServiceInterface $methodService
     */
    public function __construct(
        ConfigService $configService,
        CartRepositoryInterface $quoteRepository,
        MethodServiceInterface $methodService
    ) {
        $this->configService = $configService;
        $this->quoteRepository = $quoteRepository;
        $this->methodService = $methodService;
    }

    /**
     * @param ShippingInformationManagementInterface $subject
     * @param PaymentDetailsInterface $paymentDetails
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return PaymentDetailsInterface
     * @throws NoSuchEntityException
     */
    public function afterSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        PaymentDetailsInterface $paymentDetails,
        $cartId,
        ShippingInformationInterface $addressInformation
    ): PaymentDetailsInterface {
        if ($this->configService->isEnabled()) {
            $quote = $this->quoteRepository->getActive($cartId);

            $availablePaymentMethods = $paymentDetails->getPaymentMethods();
            $availableProfileMethods = $this->methodService->getAvailablePaymentMethods($quote);
            foreach ($availablePaymentMethods as $id => $paymentMethod) {
                if (strpos($paymentMethod->getCode(), ConfigProvider::CODE . '_') === false) {
                    continue;
                }

                if (!isset($availableProfileMethods[$paymentMethod->getCode()])) {
                    unset($availablePaymentMethods[$id]);
                }
            }

            $paymentDetails->setPaymentMethods($availablePaymentMethods);
        }

        return $paymentDetails;
    }
}
