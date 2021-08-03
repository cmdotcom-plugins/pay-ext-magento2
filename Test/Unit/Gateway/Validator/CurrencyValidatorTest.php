<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Gateway\Validator\CurrencyValidator;
use CM\Payments\Test\Unit\UnitTestCase;

class CurrencyValidatorTest extends UnitTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $resultMock;

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var CurrencyValidator
     */
    private $currencyValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resultMock = $this->getMockupFactory(
            \Magento\Payment\Gateway\Validator\Result::class,
            \Magento\Payment\Gateway\Validator\ResultInterface::class
        );

        $this->configMock = $this->getMockBuilder(\Magento\Payment\Gateway\ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->currencyValidator = new CurrencyValidator($this->resultMock, $this->configMock);
    }

    public function testAllowSpecificCurrency()
    {
        $this->configMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                ['allow_specific_currency', 1],
                ['specific_currency', 1]
            )
            ->willReturnOnConsecutiveCalls(
                '1',
                'EUR,USD'
            );

        $actual = $this->currencyValidator->validate([
            'storeId' => 1,
            'currency' => 'USD'
        ]);

        $this->assertTrue($actual->isValid());
    }

    public function testInactiveAllowSpecificCurrency()
    {
        $this->configMock
            ->expects($this->exactly(1))
            ->method('getValue')
            ->withConsecutive(
                ['allow_specific_currency', 1]
            )
            ->willReturnOnConsecutiveCalls(
                '0'
            );

        $actual = $this->currencyValidator->validate([
            'storeId' => 1,
            'currency' => 'USD'
        ]);

        $this->assertTrue($actual->isValid());
    }

    public function testSpecificCurrencyDiffersFromOrderCurrency()
    {
        $this->configMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                ['allow_specific_currency', 1],
                ['specific_currency', 1]
            )
            ->willReturnOnConsecutiveCalls(
                '1',
                'EUR'
            );

        $actual = $this->currencyValidator->validate([
            'storeId' => 1,
            'currency' => 'USD'
        ]);

        $this->assertFalse($actual->isValid());
    }

    public function testEmptySpecificCurrency()
    {
        $this->configMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                ['allow_specific_currency', 1]
            )
            ->willReturnOnConsecutiveCalls(
                '1',
                null
            );

        $actual = $this->currencyValidator->validate([
            'storeId' => 1,
            'currency' => 'USD'
        ]);

        $this->assertTrue($actual->isValid());
    }
}
