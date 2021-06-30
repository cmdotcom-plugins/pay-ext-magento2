<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Model\Data;

use CM\Payments\Api\Model\Data\PaymentMethodAdditionalDataInterface;
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
}
