<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Service\Order\Request\Part;

use CM\Payments\Api\Service\Order\Request\RequestPartByOrderInterface;
use CM\Payments\Client\Model\Request\OrderCreate;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class Language implements RequestPartByOrderInterface
{
    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * Language constructor.
     * @param ResolverInterface $localeResolver
     */
    public function __construct(ResolverInterface $localeResolver)
    {
        $this->localeResolver = $localeResolver;
    }

    /**
     * @inheritDoc
     */
    public function process(OrderInterface $order, OrderCreate $orderCreate): OrderCreate
    {
        $orderCreate->setLanguage($this->getLanguageCode((int)$order->getStoreId()));

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
