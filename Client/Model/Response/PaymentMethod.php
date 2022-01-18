<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response;

use CM\Payments\Client\Model\Response\Method\IdealIssuer;

class PaymentMethod
{
    /**
     * @var string|null
     */
    private $method;

    /**
     * @var \CM\Payments\Client\Api\IdealIssuerInterface
     */
    private $idealIssuers;

    /**
     * PaymentMethod constructor
     *
     * @param array $orderCreate
     */
    public function __construct(
        array $method
    ) {
        $this->method = $method['method'];
        $this->idealIssuers = isset($method['ideal_details']) ?
            $this->mapIssuers($method['ideal_details']['issuers']) : [];
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @return \CM\Payments\Client\Api\IdealIssuerInterface[]
     */
    public function getIdealIssuers(): array
    {
        return $this->idealIssuers;
    }

    /**
     * @param array $issuers
     * @return IdealIssuerInterface[]
     */
    private function mapIssuers(array $issuers): array
    {
        return $this->sortIdealIssuers(
            array_map(function ($issuer) {
                return new IdealIssuer($issuer);
            }, $issuers)
        );
    }

    /**
     * Sort ideal issuers asc by name
     *
     * @param IdealIssuer[] $issuerList
     * @return IdealIssuer[]
     */
    private function sortIdealIssuers(array $issuerList): array
    {
        usort($issuerList, function ($a, $b) {
            return strcmp($a->getName(), $b->getName());
        });

        return $issuerList;
    }
}
