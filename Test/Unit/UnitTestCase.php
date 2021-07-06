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
     * Customer Email Constant
     */
    public const CUSTOMER_EMAIL = 'test@cm.com';

    /**
     * Language Constant
     */
    public const LANGUAGE = 'nl';

    /**
     * Payment Profile
     */
    public const PAYMENT_PROFILE = 'test';

    /**
     * Customer Address Constant
     */
    public const ADDRESS_DATA = [
        'region'                     => '',
        'region_code'                => '',
        'country_code'               => 'NL',
        'email_address'              => self::CUSTOMER_EMAIL,
        'city'                       => 'Den Haag',
        'street_address1'            => 'Boerhaavelaan 7',
        'street_address2'            => '',
        'postal_code'                => '2500 DL',
        'phone_number'               => '0123456789',
        'company'                    => 'CM'
    ];

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
     * @param ?string $interface
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockupFactory(string $instanceName, string $interface = null)
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
