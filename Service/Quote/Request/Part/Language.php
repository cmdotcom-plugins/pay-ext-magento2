<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service\Quote\Request\Part;

use CM\Payments\Api\Service\Order\Request\RequestPartByQuoteInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Quote\Api\Data\CartInterface;

class Language implements RequestPartByQuoteInterface
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * Language constructor
     *
     * @param ResolverInterface $localeResolver
     */
    public function __construct(ResolverInterface $localeResolver)
    {
        $this->localeResolver = $localeResolver;
    }

    /**
     * @inheritDoc
     */
    public function process(CartInterface $quote, OrderCreate $orderCreate): OrderCreate
    {
        $orderCreate->setLanguage($this->getLanguageCode($quote->getStoreId()));

        return $orderCreate;
    }

    /**
     * @param int $storeId
     * @return string
     */
    private function getLanguageCode(int $storeId): string
    {
        if (!$this->localeResolver->emulate($storeId)) {
            return '';
        }

        return substr($this->localeResolver->emulate($storeId), 0, 2);
    }
}
