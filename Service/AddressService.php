<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Service\AddressServiceInterface;

class AddressService implements AddressServiceInterface
{
    /**
     * Regex Patterns for splitting house number from street
     */
    public const STREET_SPLIT_NAME_FROM_NUMBER =
        '/^(?P<street>\d*[\wäöüßÀ-ÖØ-öø-ÿĀ-Ž\d \'\‘\`\-\.]+)[,\s]+(?P<number>\d+)\s*(?P<addition>[\wäöüß\d\-\/]*)$/i';
    public const STREET_SPLIT_NUMBER_FROM_NAME =
        '/^(?P<number>\d+)\s*(?P<street>[\wäöüßÀ-ÖØ-öø-ÿĀ-Ž\d \'\‘\`\-\.]*)$/i';

    /**
     * @inheritDoc
     */
    public function process(
        array $address
    ): array {
        return $this->prepareAddress($address);
    }

    /**
     * @param array $address
     * @return array
     */
    private function prepareAddress(array $address): array
    {
        $street = $address['street'];
        $matched = preg_match(self::STREET_SPLIT_NAME_FROM_NUMBER, trim($street), $result);
        if (!$matched) {
            $matched = preg_match(self::STREET_SPLIT_NUMBER_FROM_NAME, trim($street), $result);
        }

        if ($matched && is_array($result)) {
            if ($result['street']) {
                $address['street'] = trim($result['street']);
            }

            if ($result['number']) {
                $address['housenumber'] = trim($result['number']);
            }

            if (isset($result['addition']) && $result['addition']) {
                $address['housenumber_addition'] = trim($result['addition']);
            }
        }

        $address['postalcode'] = preg_replace('/\s+/', '', $address['postalcode']);

        return $address;
    }
}
