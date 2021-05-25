<?php
/**
 * Copyright © 2021 cm.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model;

use CM\Payments\Api\Model\Data\OrderInterface;
use CM\Payments\Api\Model\OrderRepositoryInterface;
use CM\Payments\Model\ResourceModel\Order as ResourceOrder;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var ResourceOrder
     */
    private $resource;
    /**
     * @var OrderFactory
     */
    private $orderFactory;
    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * OrderRepository constructor.
     * @param ResourceOrder $resource
     */
    public function __construct(
        ResourceOrder $resource,
        OrderFactory $orderFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        OrderInterface $order
    ) {
        $orderData = $this->extensibleDataObjectConverter->toNestedArray(
            $order,
            [],
            OrderInterface::class
        );

        $orderModel = $this->orderFactory->create()->setData($orderData);

        try {
            $this->resource->save($orderModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the order: %1',
                $exception->getMessage()
            ));
        }
        return $orderModel->getDataModel();
    }

    /**
     * @inheritDoc
     */
    public function getByOrderKey(string $orderKey)
    {
        $order = $this->orderFactory->create();
        $this->resource->load($order, $orderKey);

        if (!$order->getId()) {
            throw new NoSuchEntityException(__('order with key 1" does not exist.', $orderKey));
        }

        return $order->getDataModel();
    }
}
