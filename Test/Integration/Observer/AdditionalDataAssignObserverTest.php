<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Observer;

use CM\Payments\Observer\AdditionalDataAssignObserver;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;

class AdditionalDataAssignObserverTest extends IntegrationTestCase
{
    public function testSetAdditionalData()
    {
        /** @var AdditionalDataAssignObserver $additionalDataAssignObserver */
        $additionalDataAssignObserver = $this->objectManager->create(AdditionalDataAssignObserver::class);

        $paymentInfoMock = $this->createMock(InfoInterface::class);
        $event = $this->objectManager->create(Event::class);
        $data =  $this->objectManager->create(DataObject::class);
        $data->setData('additional_data', [
            'selected_issuer' => 'ING'
        ]);
        $event->setData('payment_model', $paymentInfoMock);
        $event->setData('data', $data);

        /** @var Observer $observer */
        $observer = $this->objectManager->create(Observer::class, ['event' => $event]);
        $observer->setEvent($event);
        $paymentInfoMock->expects($this->once())->method('setAdditionalInformation')->with('selected_issuer', 'ING');

        $additionalDataAssignObserver->execute($observer);
    }

    public function testDoNothingWhenAdditionalDataIsNotAnArray()
    {
        /** @var AdditionalDataAssignObserver $additionalDataAssignObserver */
        $additionalDataAssignObserver = $this->objectManager->create(AdditionalDataAssignObserver::class);

        $paymentInfoMock = $this->createMock(InfoInterface::class);
        $event = $this->objectManager->create(Event::class);
        $data =  $this->objectManager->create(DataObject::class);
        $data->setData('additional_data', 'no array');
        $event->setData('payment_model', $paymentInfoMock);
        $event->setData('data', $data);

        /** @var Observer $observer */
        $observer = $this->objectManager->create(Observer::class, ['event' => $event]);
        $observer->setEvent($event);
        $paymentInfoMock->expects($this->never())->method('setAdditionalInformation');

        $additionalDataAssignObserver->execute($observer);
    }
}
