<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Model;

use CM\Payments\Config\Config as ConfigService;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Model\Adminhtml\Source\Cctype as CcTypeSource;
use CM\Payments\Model\ConfigProvider;
use CM\Payments\Test\Integration\IntegrationTestCase;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Asset\Source as AssetSource;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigProviderTest extends IntegrationTestCase
{
    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoConfigFixture default_store payment/cm_payments_kbc/active 1
     * @magentoConfigFixture default_store payment/cm_payments_cbc/active 1
     * @magentoConfigFixture default_store payment/cm_payments_belfius/active 1
     *
     */
    public function testGetConfig()
    {
        $assertSourceMock = $this->getMockBuilder(AssetSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $assertSourceMock->method('findSource')->willReturn(null);

        /** @var ConfigProvider|MockObject $configProviderInstance */
        $configProviderInstance = $this->objectManager->create(
            ConfigProvider::class,
            [
                'assetRepository' => $this->objectManager->create(AssetRepository::class),
                'assetSource'     => $assertSourceMock,
                'configService'   => $this->objectManager->create(ConfigService::class),
                'ccTypeSource'    => $this->objectManager->create(CcTypeSource::class),
                'logger'          => $this->objectManager->create(CMPaymentsLogger::class, ['name' => 'CMPayments']),
            ]
        );

        $result = $configProviderInstance->getConfig();

        $this->assertTrue($result['payment']['cm_payments']['is_enabled']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_creditcard']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_ideal']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_paypal']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_bancontact']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_kbc']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_cbc']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_belfius']);
    }
}
