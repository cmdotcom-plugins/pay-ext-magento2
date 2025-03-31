<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Payment Method  interface
 *
 * @api
 */
interface PaymentMethodAdditionalDataInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \CM\Payments\Api\Data\PaymentMethodAdditionalDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?PaymentMethodAdditionalDataExtensionInterface;

    /**
     * Set an extension attributes object.
     *
     * @param \CM\Payments\Api\Data\PaymentMethodAdditionalDataExtensionInterface $extensionAttributes
     * @return PaymentMethodAdditionalDataInterface
     */
    public function setExtensionAttributes(
        PaymentMethodAdditionalDataExtensionInterface $extensionAttributes
    ): PaymentMethodAdditionalDataInterface;
}
