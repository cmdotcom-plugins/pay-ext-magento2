<?php

namespace CM\Payments\Api\Model\Domain;

interface CMOrderInterface
{
    public function getUrl(): string;

    public function getOrderReference(): string;

    public function getOrderKey(): string;

    public function getExpiresOn(): string;
}
