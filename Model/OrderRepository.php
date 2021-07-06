<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model;

use CM\Payments\Model\Order as CMOrder;
use CM\Payments\Model\OrderFactory as CMOrderFactory;
use CM\Payments\Api\Model\Data\OrderInterface;
use CM\Payments\Api\Model\OrderRepositoryInterface;
use CM\Payments\Model\ResourceModel\Order as ResourceOrder;
use Exception;
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
     * @var CMOrderFactory
     */
    private $cmOrderFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * OrderRepository constructor
     *
     * @param ResourceOrder $resource
     * @param CMOrderFactory $cmOrderFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceOrder $resource,
        CMOrderFactory $cmOrderFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->cmOrderFactory = $cmOrderFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save order
     *
     * @param OrderInterface $order
     * @return mixed
     * @throws CouldNotSaveException
     */
    public function save(OrderInterface $order): OrderInterface
    {
        $orderData = $this->extensibleDataObjectConverter->toNestedArray(
            $order,
            [],
            OrderInterface::class
        );

        /** @var CMOrder $orderModel */
        $orderModel = $this->cmOrderFactory->create()->setData($orderData);

        try {
            $this->resource->save($orderModel);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the order: %1',
                $exception->getMessage()
            ));
        }

        return $orderModel->getDataModel();
    }

    /**
     * @param string $incrementId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    public function getByIncrementId(string $incrementId): OrderInterface
    {
        /** @var CMOrder $orderModel */
        $orderModel = $this->cmOrderFactory->create();
        $this->resource->load($orderModel, $incrementId, 'increment_id');

        if (!$orderModel->getId()) {
            throw new NoSuchEntityException(__('Order with key %1 does not exist.', $incrementId));
        }

        return $orderModel->getDataModel();
    }

    /**
     * @param string $orderKey
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderKey(string $orderKey): OrderInterface
    {

        /** @var CMOrder $orderModel */
        $orderModel = $this->cmOrderFactory->create();
        $this->resource->load($orderModel, $orderKey, 'order_key');

        if (!$orderModel->getId()) {
            throw new NoSuchEntityException(__('Order with key %1 does not exist.', $orderKey));
        }

        return $orderModel->getDataModel();
    }
}
