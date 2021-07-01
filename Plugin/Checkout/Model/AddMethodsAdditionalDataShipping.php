<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Plugin\Checkout\Model;

use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Config\Config as ConfigService;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class AddMethodsAdditionalDataShipping
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

            return $this->methodService->addMethodAdditionalData($quote, $paymentDetails);
        }

        return $paymentDetails;
    }
}
