<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Quote\Item\Request\Part;

use CM\Payments\Api\Service\Order\Item\Request\RequestPartByQuoteItemInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use Magento\Quote\Api\Data\CartItemInterface;

class VatAmount implements RequestPartByQuoteItemInterface
{
    /**
     * @inheritDoc
     */
    public function process(CartItemInterface $quoteItem, OrderItemCreate $orderItemCreate): OrderItemCreate
    {
        $taxPercent = (float)$orderItemCreate->getVatRate();
        $vatAmount = round(
            ($orderItemCreate->getAmount() / 100) * ($taxPercent / (100 + $taxPercent)
            ),
            2
        );

        $orderItemCreate->setVatAmount((int)($vatAmount * 100));

        return $orderItemCreate;
    }
}
