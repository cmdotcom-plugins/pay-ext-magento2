<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Controller;

use Magento\TestFramework\TestCase\AbstractController;

class SwaggerTest extends AbstractController
{
    public function testSwaggerEndpoint()
    {
        // Get rest api output
        ob_start();
        $this->dispatch('rest/all/schema?services=all');
        $body = (string) ob_get_contents();
        ob_end_clean();

        $actual = json_decode($body, true);
        $this->assertEquals(200, $this->getResponse()->getStatusCode());
        $this->assertArrayNotHasKey('message', $actual);
        $this->assertArrayHasKey('swagger', $actual);
    }
}
