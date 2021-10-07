<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client\Model\Response;

use CM\Payments\Client\Model\CMPaymentUrl;

class PaymentCreate
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $status;

    /**
     * @var CMPaymentUrl[]
     */
    private $urls;

    /**
     * PaymentCreate constructor
     *
     * @param array $paymentCreate
     */
    public function __construct(
        array $paymentCreate
    ) {
        $this->id = $paymentCreate['id'] ?? null;
        $this->status = $paymentCreate['status'] ?? null;
        if (!empty($paymentCreate['urls'])) {
            foreach ($paymentCreate['urls'] as $url) {
                $this->urls[] = new CMPaymentUrl(
                    (string)$url['purpose'],
                    (string)$url['method'],
                    (string)$url['url'],
                    (string)$url['order'],
                    $url['parameters'] ?: ''
                );
            }
        } else {
            $this->urls = [];
        }
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return CMPaymentUrl[]
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        /** @var CMPaymentUrl $paymentUrl */
        foreach ($this->urls as $paymentUrl) {
            if ($paymentUrl->getPurpose() === CMPaymentUrl::PURPOSE_REDIRECT) {
                return $paymentUrl->getUrl();
            }
        }

        return null;
    }
}
