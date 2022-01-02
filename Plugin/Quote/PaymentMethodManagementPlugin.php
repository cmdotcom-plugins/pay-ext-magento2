<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Plugin\Quote;

use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Api\Service\OrderServiceInterface;
use CM\Payments\Client\Model\Response\OrderCreate;
use CM\Payments\Config\Config as ConfigService;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\PaymentMethodManagement;

class PaymentMethodManagementPlugin
{
    /**
     * @var OrderCreate|null
     */
    protected $cmOrder = null;
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
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var OrderServiceInterface
     */
    private $orderService;

    /**
     * AddMethodsAdditionalData constructor
     *
     * @param ConfigService $configService
     * @param CartRepositoryInterface $quoteRep ository
     * @param MethodServiceInterface $methodService
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ConfigService $configService,
        CartRepositoryInterface $quoteRepository,
        MethodServiceInterface $methodService,
        OrderServiceInterface $orderService,
        ManagerInterface $eventManager
    ) {
        $this->configService = $configService;
        $this->quoteRepository = $quoteRepository;
        $this->methodService = $methodService;
        $this->eventManager = $eventManager;
        $this->orderService = $orderService;
    }

    /**
     * @param PaymentMethodManagement $subject
     * @param int $cartId
     * @return int
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeGetList(
        PaymentMethodManagement $subject,
        int $cartId
    ): int {
        if ($this->configService->isEnabled()) {
            $quote = $this->quoteRepository->getActive($cartId);
            $this->cmOrder = $this->orderService->createByQuote($quote);
            $quote->setData('cm_order_key', $this->cmOrder->getOrderKey());
            $this->quoteRepository->save($quote);
        }

        return $cartId;
    }

    /**
     * @param PaymentMethodManagement $subject
     * @param \Magento\Quote\Api\Data\PaymentMethodInterface[] $availableMethods
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetList(
        PaymentMethodManagement $subject,
        array $availableMethods
    ): array {
        if ($this->configService->isEnabled()) {
            $cmPaymentMethods = $this->methodService->getCmMethods($this->cmOrder->getOrderKey());

            return $this->methodService->filterMethods($availableMethods, $cmPaymentMethods);
        }

        return $availableMethods;
    }
}
