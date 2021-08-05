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

class Gender implements RequestPartByOrderAddressInterface
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
        $shopperCreate->setGender('');
        if ($orderAddress->getCustomerId()) {
            try {
                $customer = $this->customerRepository->getById($orderAddress->getCustomerId());

                if ($customer->getGender()) {
                    $shopperCreate->setGender(ShopperCreate::GENDERS_MAPPING[$customer->getGender()]);
                }
            } catch (LocalizedException | NoSuchEntityException $e) {
                $shopperCreate->setGender('');
            }
        }

        //TODO: Replace on 'U' when will be fixed issue in API. Temporary solution, because the gender is mandatory.
        if (!$shopperCreate->getGender()) {
            $shopperCreate->setGender(ShopperCreate::GENDER_MALE);
        }

        return $shopperCreate;
    }
}
