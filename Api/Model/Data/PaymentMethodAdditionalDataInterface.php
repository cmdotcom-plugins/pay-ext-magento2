<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Model\Data;

/**
 * Payment Method  interface
 *
 * @api
 */
interface PaymentMethodAdditionalDataInterface
{
    /**
     * Properties
     */
    public const ISSUERS = 'issuers';

    /**
     * Get issuers
     *
     * @return \CM\Payments\Api\Model\Data\IssuerInterface[]
     */
    public function getIssuers(): array;

    /**
     * Set code
     *
     * @param \CM\Payments\Api\Model\Data\IssuerInterface[] $issuers
     * @return PaymentMethodAdditionalDataInterface
     */
    public function setIssuers(array $issuers): PaymentMethodAdditionalDataInterface;
}
