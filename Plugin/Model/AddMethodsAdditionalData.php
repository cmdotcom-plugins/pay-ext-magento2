<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Plugin\Model;

use CM\Payments\Api\PaymentMethodManagementInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Config\Config as ConfigService;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;

class AddMethodsAdditionalData
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
     * AddMethodsAdditionalData constructor
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
     * @param PaymentMethodManagementInterface $subject
     * @param PaymentDetailsInterface $paymentDetails
     * @param int $cartId
     * @param ?AddressInterface $shippingAddress
     * @return PaymentDetailsInterface
     * @throws NoSuchEntityException
     */
    public function afterGetPaymentMethods(
        PaymentMethodManagementInterface $subject,
        PaymentDetailsInterface $paymentDetails,
        int $cartId,
        ?AddressInterface $shippingAddress
    ): PaymentDetailsInterface {
        if ($this->configService->isEnabled()) {
            $quote = $this->quoteRepository->getActive($cartId);

            return $this->methodService->addMethodAdditionalData($quote, $paymentDetails);
        }

        return $paymentDetails;
    }
}
