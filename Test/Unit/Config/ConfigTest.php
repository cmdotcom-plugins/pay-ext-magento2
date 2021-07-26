<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Unit\Config;

use CM\Payments\Config\Config;
use CM\Payments\Test\Unit\UnitTestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

class ConfigTest extends UnitTestCase
{
    /**
     * @var StoreManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManagerMock;
    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigMock;
    /**
     * @var Config
     */
    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStore = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockStore->method('getId')->willReturn('1');
        $this->storeManagerMock->method('getStore')->willReturn($mockStore);

        $this->config = new Config($this->scopeConfigMock, $this->storeManagerMock);
    }

    public function testGetPaymentProfileTestCreditCard()
    {
        $this->scopeConfigMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive([
                'payment/cm_payments_methods/profile',
                'stores',
                '1'
            ], [
                'payment/cm_payments_creditcard/profile',
                'stores',
                '1'
            ])
            ->willReturnOnConsecutiveCalls(
                'default',
                'CreditCard'
            );

        $actual = $this->config->getPaymentProfile('cm_payments_creditcard');

        $this->assertEquals('CreditCard', $actual);
    }

    public function testGetPaymentProfileTestFallbackToDefault()
    {
        $this->scopeConfigMock
            ->expects($this->exactly(1))
            ->method('getValue')
            ->withConsecutive([
                'payment/cm_payments_methods/profile',
                'stores',
                '1'
            ])
            ->willReturnOnConsecutiveCalls(
                'default'
            );

        $actual = $this->config->getPaymentProfile('bla');

        $this->assertEquals('default', $actual);
    }

    public function testGetPaymentProfileTestCreditCardFallbackToDefault()
    {
        $this->scopeConfigMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive([
                'payment/cm_payments_methods/profile',
                'stores',
                '1'
            ], [
                'payment/cm_payments_creditcard/profile',
                'stores',
                '1'
            ])
            ->willReturnOnConsecutiveCalls(
                'default',
                null
            );

        $actual = $this->config->getPaymentProfile('cm_payments_creditcard');

        $this->assertEquals('default', $actual);
    }

    public function testGetPaymentProfileBanContact()
    {
        $this->scopeConfigMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive([
                'payment/cm_payments_methods/profile',
                'stores',
                '1'
            ], [
                'payment/cm_payments_bancontact/profile',
                'stores',
                '1'
            ])
            ->willReturnOnConsecutiveCalls(
                'default',
                'bancontact_profile'
            );

        $actual = $this->config->getPaymentProfile('cm_payments_bancontact');

        $this->assertEquals('bancontact_profile', $actual);
    }

    public function testGetPaymentProfileCmPaymentsRedirect()
    {
        $this->scopeConfigMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive([
                'payment/cm_payments_methods/profile',
                'stores',
                '1'
            ], [
                'payment/cm_payments/profile',
                'stores',
                '1'
            ])
            ->willReturnOnConsecutiveCalls(
                'default',
                'all'
            );

        $actual = $this->config->getPaymentProfile('cm_payments');

        $this->assertEquals('all', $actual);
    }
}
