<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Api\Model\Data;

/**
 * Interface IssuerInterface
 *
 * @api
 */
interface IssuerInterface
{
    /**
     * Properties
     */
    public const CODE = 'code';
    public const TITLE = 'title';

    /**
     * Get code
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Set code
     *
     * @param string $code
     * @return IssuerInterface
     */
    public function setCode(string $code): IssuerInterface;

    /**
     * Set title
     *
     * @param string $title
     * @return IssuerInterface
     */
    public function setTitle(string $title): IssuerInterface;
}
