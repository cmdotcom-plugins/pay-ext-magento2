<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model;

use CM\Payments\Api\Model\Data\PaymentInterface;
use CM\Payments\Api\Model\PaymentRepositoryInterface;
use CM\Payments\Client\Model\CMPayment;
use CM\Payments\Model\PaymentFactory as CMPaymentFactory;
use CM\Payments\Api\Model\DataPaymentInterface;
use CM\Payments\Model\ResourceModel\Payment as ResourcePayment;
use Exception;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\CouldNotSaveException;

class PaymentRepository implements PaymentRepositoryInterface
{
    /**
     * @var ResourcePayment
     */
    private $resource;

    /**
     * @var CMPaymentFactory
     */
    private $cmPaymentFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * OrderRepository constructor
     *
     * @param ResourcePayment $resource
     * @param CMPaymentFactory $cmPaymentFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourcePayment $resource,
        CMPaymentFactory $cmPaymentFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->cmPaymentFactory = $cmPaymentFactory;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Save payment
     *
     * @param PaymentInterface $payment
     * @return mixed
     * @throws CouldNotSaveException
     */
    public function save(PaymentInterface $payment): PaymentInterface
    {
        $paymentData = $this->extensibleDataObjectConverter->toNestedArray(
            $payment,
            [],
            PaymentInterface::class
        );

        /** @var CMPayment $paymentModel */
        $paymentModel = $this->cmPaymentFactory->create()->setData($paymentData);

        try {
            $this->resource->save($paymentModel);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the order: %1',
                $exception->getMessage()
            ));
        }

        return $paymentModel->getDataModel();
    }
}
