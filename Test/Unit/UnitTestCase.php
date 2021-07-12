<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = new ObjectManager($this);
        $this->setUpWithoutVoid();
    }

    protected function setUpWithoutVoid()
    {
    }

    /**
     * @param string $instanceName
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockupFactory(string $instanceName, $interface = null)
    {
        $className = $interface ?? $instanceName;
        $orderFactoryMock = $this->getMockBuilder($className . 'Factory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $orderFactoryMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(function ($args = []) use ($instanceName) {
                return $this->objectManager->getObject($instanceName, $args);
            }));

        return $orderFactoryMock;
    }
}
