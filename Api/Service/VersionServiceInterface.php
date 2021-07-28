<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Service;

interface VersionServiceInterface
{
    /**
     * Repository Vendor Name
     */
    public const REPOSITORY_VENDOR_NAME = 'cmdotcom';

    /**
     * Repository Extension Name
     */
    public const REPOSITORY_EXTENSION_NAME = 'pay-ext-magento2';

    /**
     */
    public function getLatestVersion();

    /**
     * Get Repository Url
     *
     * @return string
     */
    public function getRepositoryUrl(): string;
}
