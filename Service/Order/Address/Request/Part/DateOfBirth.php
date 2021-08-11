<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Order\Address\Request\Part;

use CM\Payments\Api\Service\Shopper\Request\RequestPartByOrderAddressInterface;
use CM\Payments\Client\Model\Request\ShopperCreate;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderAddressInterface;

class DateOfBirth implements RequestPartByOrderAddressInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * DateOfBirth constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     */
    public function process(OrderAddressInterface $orderAddress, ShopperCreate $shopperCreate): ShopperCreate
    {
        $dob = $orderAddress->getOrder()->getPayment()->getAdditionalInformation('dob');
        if (!empty($dob)) {
            $shopperCreate->setDateOfBirth((string)$dob);
        } elseif ($orderAddress->getCustomerId()) {
            try {
                $customer = $this->customerRepository->getById($orderAddress->getCustomerId());
                $shopperCreate->setDateOfBirth((string)$customer->getDob());
            } catch (LocalizedException | NoSuchEntityException $e) {
                $shopperCreate->setDateOfBirth('');
            }
        } else {
            $shopperCreate->setDateOfBirth('');
        }

        return $shopperCreate;
    }
}
