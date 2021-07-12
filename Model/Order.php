<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model;

use CM\Payments\Api\Model\Data\OrderInterface as CMOrderInterface;
use CM\Payments\Api\Model\Data\OrderInterfaceFactory as CMOrderInterfaceFactory;
use CM\Payments\Model\ResourceModel\Order as ResourceOrder;
use CM\Payments\Model\ResourceModel\Order\Collection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Order extends AbstractModel
{
    public const CACHE_TAG = 'cm_payments_order';

    /**
     * @var string
     */
    protected $_eventPrefix = 'cm_payments_order';

    /**
     * @var CMOrderInterfaceFactory
     */
    private $cmOrderDataFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CMOrderInterfaceFactory $cmOrderDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceOrder $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CMOrderInterfaceFactory $cmOrderDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceOrder $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->cmOrderDataFactory = $cmOrderDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Retrieve CM order model with CM Order data
     *
     * @return CMOrderInterface
     */
    public function getDataModel(): CMOrderInterface
    {
        $cmOrderData = $this->getData();

        $cmOrderDataObject = $this->cmOrderDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $cmOrderDataObject,
            $cmOrderData,
            CMOrderInterface::class
        );

        return $cmOrderDataObject;
    }

    protected function _construct()
    {
        $this->_init(ResourceOrder::class);
    }
}
