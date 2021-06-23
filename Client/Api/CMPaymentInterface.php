<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Api;

use CM\Payments\Client\Model\CMPaymentUrl;

interface CMPaymentInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return CMPaymentUrl[]
     */
    public function getUrls(): array;
}
