<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

class IntegrationTestCase extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup function
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @param string $orderId
     * @return OrderInterface
     */
    protected function loadOrderById($orderId)
    {
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', $orderId, 'eq')->create();

        $orderList = $orderRepository->getList($searchCriteria)->getItems();

        return array_shift($orderList);
    }

    /**
     * @param string $orderId
     * @return CartInterface
     */
    protected function loadQuoteById($orderId)
    {
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $orderId, 'eq')->create();

        $orderList = $quoteRepository->getList($searchCriteria)->getItems();

        return array_shift($orderList);
    }

    /**
     * Adds currency code to order
     *
     * @param OrderInterface $magentoOrder
     * @return OrderInterface
     */
    protected function addCurrencyToOrder(OrderInterface $magentoOrder): OrderInterface
    {
        /** @var OrderInterface $magentoOrder */
        $magentoOrder
            ->setOrderCurrencyCode('EUR')
            ->setBaseCurrencyCode('EUR');

        $repository = $this->objectManager->get(OrderRepositoryInterface::class);
        $repository->save($magentoOrder);

        return $magentoOrder;
    }
}
