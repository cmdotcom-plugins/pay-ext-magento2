<?php
/**
 * Copyright © 2021 cm.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Gateway\Request\Builder;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order;

class RedirectTransactionBuilder implements BuilderInterface
{
    /**
     * @var State
     */
    private $state;

    /**
     * RedirectTransactionBuilder constructor.
     *
     * @param State $state
     */
    public function __construct(
        State $state
    ) {
        $this->state = $state;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $stateObject = $buildSubject['stateObject'];

        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $order = $payment->getOrder();

        $state = Order::STATE_NEW;

        $stateObject->setState($state);

        // Early return on backend order
        if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
            return [];
        }

        return [];
    }
}
