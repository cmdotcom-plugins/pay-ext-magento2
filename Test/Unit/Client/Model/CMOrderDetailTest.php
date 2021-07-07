<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Client\Model\Response\OrderDetail;
use CM\Payments\Test\Mock\MockApiResponse;
use CM\Payments\Test\Unit\UnitTestCase;

class CMOrderDetailTest extends UnitTestCase
{
    /**
     * @var MockApiResponse
     */
    private $mockApiResponse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockApiResponse = new MockApiResponse();
    }

    public function testIsSafeReturnsTrue()
    {
        $cmOrderDetail = new OrderDetail(
            $this->mockApiResponse->getOrderDetail()
        );

        $actual = $cmOrderDetail->isSafe();

        $this->assertSame(true, $actual);
    }

    public function testIsSafeReturnsFalse()
    {
        $cmOrderDetail = new OrderDetail(
            $this->mockApiResponse->getOrderDetailConsideredFast()
        );

        $actual = $cmOrderDetail->isSafe();

        $this->assertSame(false, $actual);
    }

    public function testAuthorizedPayment()
    {
        $orderDetail = new OrderDetail(
            $this->mockApiResponse->getOrderDetail()
        );

        $actual = $orderDetail->getAuthorizedPayment();

        $this->assertSame('pid4911203603t', $actual->getId());
    }

    public function testEmptyPayment()
    {
        $orderDetail = new OrderDetail(
            $this->mockApiResponse->getOrderDetaulWithoutPayment()
        );

        $this->assertSame([], $orderDetail->getPayments());
    }
}
