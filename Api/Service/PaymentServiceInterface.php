<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

use CM\Payments\Api\Data\BrowserDetailsInterface;
use CM\Payments\Api\Data\CardDetailsInterface;
use CM\Payments\Client\Api\CMPaymentInterface;
use CM\Payments\Exception\EmptyPaymentIdException;
use Magento\Framework\Exception\NoSuchEntityException;

interface PaymentServiceInterface
{
    /**
     * @param string $orderId
     * @return CMPaymentInterface
     * @throws NoSuchEntityException
     * @throws EmptyPaymentIdException
     */
    public function create(string $orderId): CMPaymentInterface;

    /**
     * @param string $quoteId
     * @param CardDetailsInterface $cardDetails
     * @param BrowserDetailsInterface $browserDetails
     *
     * @return \CM\Payments\Client\Api\CMPaymentInterface
     *
     * @throws NoSuchEntityException
     * @throws EmptyPaymentIdException
     */
    public function createByCardDetails(
        string $quoteId,
        CardDetailsInterface
        $cardDetails,
        BrowserDetailsInterface
        $browserDetails
    ): CMPaymentInterface;
}
