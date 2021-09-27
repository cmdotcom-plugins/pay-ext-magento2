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
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class AddMethodsAdditionalDataPayment
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
     * AddMethodsAdditionalDataPayment constructor
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

            return $this->methodService->addMethodAdditionalData($quote, $paymentDetails);
        }

        return $paymentDetails;
    }
}
