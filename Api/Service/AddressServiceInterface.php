<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

interface AddressServiceInterface
{
    /**
     * @param array $address
     * @return array
     */
    public function process(
        array $address
    ): array;
}
