<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Plugin\Quote;

use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Config\Config as ConfigService;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentMethodInterface;
use Magento\Quote\Model\PaymentMethodManagement;

class PaymentMethodManagementPlugin
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
     * @param PaymentMethodManagement $subject
     * @param PaymentMethodInterface[] $availableMethods
     * @param int $cartId
     * @return PaymentMethodInterface[]
     * @throws NoSuchEntityException
     */
    public function afterGetList(PaymentMethodManagement $subject, array $availableMethods, int $cartId)
    {
        if ($this->configService->isEnabled() && $this->configService->isAvailablePaymentMethodsCheckEnabled()) {
            $quote = $this->quoteRepository->getActive($cartId);

            return $this->methodService->getMethodsByQuote($quote, $availableMethods);
        }

        return $availableMethods;
    }
}
