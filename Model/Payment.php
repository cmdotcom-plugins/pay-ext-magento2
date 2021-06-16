<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model;

use CM\Payments\Api\Model\Data\PaymentInterface as CMPaymentInterface;
use CM\Payments\Api\Model\Data\PaymentInterfaceFactory as CMPaymentInterfaceFactory;
use CM\Payments\Model\ResourceModel\Payment as ResourcePayment;
use CM\Payments\Model\ResourceModel\Payment\Collection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Payment extends AbstractModel
{
    public const CACHE_TAG = 'cm_payments_payment';

    /**
     * @var string
     */
    protected $_eventPrefix = 'cm_payments_payment';

    /**
     * @var CMPaymentInterfaceFactory
     */
    private $cmPaymentDataFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CMPaymentInterfaceFactory $cmPaymentDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourcePayment $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CMPaymentInterfaceFactory $cmPaymentDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourcePayment $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->cmPaymentDataFactory = $cmPaymentDataFactory;
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
     * Retrieve CM payment model with CM Payment data
     *
     * @return CMPaymentInterface
     */
    public function getDataModel(): CMPaymentInterface
    {
        $cmPaymentData = $this->getData();

        $cmPaymentDataObject = $this->cmPaymentDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $cmPaymentDataObject,
            $cmPaymentData,
            CMPaymentInterface::class
        );

        return $cmPaymentDataObject;
    }

    protected function _construct()
    {
        $this->_init(ResourcePayment::class);
    }
}
