<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model;

use CM\Payments\Api\Data\PaymentMethodAdditionalDataInterface;
use CM\Payments\Api\Data\PaymentMethodAdditionalDataExtensionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class PaymentMethodAdditionalData extends AbstractExtensibleModel implements PaymentMethodAdditionalDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIssuers(): array
    {
        return $this->getData(self::ISSUERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setIssuers(array $issuers): PaymentMethodAdditionalDataInterface
    {
        return $this->setData(self::ISSUERS, $issuers);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes(): ?PaymentMethodAdditionalDataExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        PaymentMethodAdditionalDataExtensionInterface $extensionAttributes
    ): PaymentMethodAdditionalDataInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
