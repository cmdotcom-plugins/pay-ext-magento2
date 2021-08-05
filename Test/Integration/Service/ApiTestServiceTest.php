<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Integration\Service;

use CM\Payments\Client\Api\OrderInterface;
use CM\Payments\Client\Model\CMPaymentUrlFactory;
use CM\Payments\Service\ApiTestService;
use CM\Payments\Test\Integration\IntegrationTestCase;
use GuzzleHttp\ClientFactory;

class ApiTestServiceTest extends IntegrationTestCase
{
    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store cm_payments/general/mode test
     * @magentoConfigFixture default_store cm_payments/general/test_merchant_name 1
     * @magentoConfigFixture default_store cm_payments/general/test_merchant_password 1
     * @magentoConfigFixture default_store cm_payments/general/test_merchant_key 1
     */
    public function testApiConnection()
    {
        $orderClientMock = $this->createMock(OrderInterface::class);
        $orderClientMock->expects($this->once())->method('getList')->willReturn([]);
        $apiTestService = $this->objectManager->create(ApiTestService::class, ['orderClient' => $orderClientMock]);

        $actual = $apiTestService->testApiConnection();

        $this->assertArrayHasKey('result', $actual);
        $this->assertEquals([], $actual['result']);
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     */
    public function testEmptyApiCredentials()
    {
        $orderClientMock = $this->createMock(OrderInterface::class);
        $orderClientMock->expects($this->never())->method('getList');
        $apiTestService = $this->objectManager->create(ApiTestService::class, ['orderClient' => $orderClientMock]);

        $actual = $apiTestService->testApiConnection();

        $this->assertIsArray($actual['errors']);
        $firstError = $actual['errors'][0];
        $this->assertEquals($firstError, __("The 'Merchant Name' was not recognized. Please, check the data."));
    }
}
