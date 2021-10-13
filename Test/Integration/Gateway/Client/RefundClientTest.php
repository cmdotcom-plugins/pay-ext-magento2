<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace CM\Payments\Test\Integration\Gateway\Client;

use CM\Payments\Client\Model\Request\RefundCreate;
use CM\Payments\Client\Refund;
use CM\Payments\Gateway\Http\Client\RefundClient;
use CM\Payments\Test\Integration\IntegrationTestCase;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Magento\Payment\Gateway\Http\TransferInterface;

class RefundClientTest extends IntegrationTestCase
{
    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 1
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 1
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/creditmemo_for_get.php
     */
    public function testClientExceptionMessage()
    {
        $refundMock = $this->createMock(Refund::class);
        $requestMock = $this->createMock(Request::class);
        $response = new Response(401, [], '{"messages: ["error message"]}');
        $refundMock->expects($this->once())->method('refund')->willThrowException(new ClientException('message',$requestMock, $response));

        $refundClient = $this->objectManager->create(RefundClient::class);
        $transferMock = $this->createMock(TransferInterface::class);

        $this->expectExceptionMessage('error message');
        $refundClient->placeRequest($transferMock);
    }
}
