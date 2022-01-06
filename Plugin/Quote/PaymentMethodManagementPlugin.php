<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Plugin\Quote;

use CM\Payments\Api\Model\Domain\CMOrderInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Config\Config as ConfigService;
use Magento\Quote\Api\CartRepositoryInterface;
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
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * AddMethodsAdditionalData constructor
     *
     * @param ConfigService $configService
     * @param CartRepositoryInterface $quoteRepository
     * @param MethodServiceInterface $methodService
     * @param OrderServiceInterface $orderService
     */
    public function __construct(
        ConfigService $configService,
        CartRepositoryInterface $quoteRepository,
        MethodServiceInterface $methodService,
        OrderServiceInterface $orderService
    ) {
        $this->configService = $configService;
        $this->quoteRepository = $quoteRepository;
        $this->methodService = $methodService;
        $this->orderService = $orderService;
    }

    /**
     * @param PaymentMethodManagement $subject
     * @param callable $proceed
     * @param int $cartId
     * @return \Magento\Quote\Api\Data\PaymentMethodInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetList(PaymentMethodManagement $subject, callable $proceed, int $cartId)
    {
        $availableMethods = $proceed($cartId);

        if ($this->configService->isEnabled()) {
            $quote = $this->quoteRepository->getActive($cartId);

            return $this->methodService->getMethodsByQuote($quote, $availableMethods);
        }

        return $availableMethods;
    }
}
