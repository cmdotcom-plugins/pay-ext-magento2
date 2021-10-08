<?php

/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Request;

class EncryptedCardDetails
{
    /**
     * @var string
     */
    private $data;

    /**
     * @param string $data
     */
    public function __construct(
        string $data
    ) {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data
        ];
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     * @return EncryptedCardDetails
     */
    public function setData(string $data): EncryptedCardDetails
    {
        $this->data = $data;
        return $this;
    }
}
