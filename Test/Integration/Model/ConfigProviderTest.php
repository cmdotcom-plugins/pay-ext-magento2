<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Model;

use CM\Payments\Model\ConfigProvider;
use CM\Payments\Test\Integration\IntegrationTestCase;

class ConfigProviderTest extends IntegrationTestCase
{
    /**
     * @magentoConfigFixture default_store payment/cm_payments_general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     */
    public function testGetConfig()
    {
        /** @var ConfigProvider $instance */
        $instance = $this->objectManager->get(ConfigProvider::class);

        $result = $instance->getConfig();

        $this->assertTrue($result['payment']['cm_payments']['is_enabled']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_creditcard']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_ideal']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_paypal']);
        $this->assertArrayHasKey('image', $result['payment']['cm_payments_bancontact']);
    }
}
