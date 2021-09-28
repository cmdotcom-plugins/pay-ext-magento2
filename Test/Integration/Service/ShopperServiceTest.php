<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Integration\Service;

use CM\Payments\Api\Service\ShopperServiceInterface;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Shopper;
use CM\Payments\Service\ShopperService;
use CM\Payments\Test\Integration\IntegrationTestCase;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;

class ShopperServiceTest extends IntegrationTestCase
{
    /**
     * @var ApiClientInterface|MockObject
     */
    private $clientMock;

    /**
     * @var ShopperServiceInterface
     */
    private $shopperService;

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testCreateShopperByQuote()
    {
        $this->clientMock->expects($this->exactly(1))->method('execute')->willReturn(
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee51',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
            ]
        );

        $magentoQuote = $this->loadQuoteById('test01');

        $actual = $this->shopperService->createByQuoteAddress($magentoQuote->getBillingAddress());
        $this->assertEquals('ec11cd24-e667-4f9e-a677-5ffe0d4aee51', $actual->getShopperKey());
        $this->assertEquals('ec11cd24-e667-4f9e-a677-5ffe0d4aee5e', $actual->getAddressKey());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote_with_customer.php
     */
    public function testCreateShopperByQuoteWithCustomer()
    {
        $this->clientMock->expects($this->exactly(1))->method('execute')->willReturn(
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee51',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e'
            ]
        );

        $magentoQuote = $this->loadQuoteById('test01');

        $actual = $this->shopperService->createByQuoteAddress($magentoQuote->getBillingAddress());
        $this->assertEquals('ec11cd24-e667-4f9e-a677-5ffe0d4aee51', $actual->getShopperKey());
        $this->assertEquals('ec11cd24-e667-4f9e-a677-5ffe0d4aee5e', $actual->getAddressKey());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testCreateShopperByOrder()
    {
        $this->clientMock->expects($this->exactly(1))->method('execute')->willReturn(
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee51'
            ]
        );

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);

        $actual = $this->shopperService->createByOrderAddress($magentoOrder->getBillingAddress());

        $this->assertEquals('ec11cd24-e667-4f9e-a677-5ffe0d4aee5e', $actual->getShopperKey());
        $this->assertEquals('ec11cd24-e667-4f9e-a677-5ffe0d4aee51', $actual->getAddressKey());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     */
    public function testCreateShopperByOrderWithCustomer()
    {
        $this->clientMock->expects($this->exactly(1))->method('execute')->willReturn(
            [
                'shopper_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee5e',
                'address_key' => 'ec11cd24-e667-4f9e-a677-5ffe0d4aee51'
            ]
        );

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);

        $actual = $this->shopperService->createByOrderAddress($magentoOrder->getBillingAddress());

        $this->assertEquals('ec11cd24-e667-4f9e-a677-5ffe0d4aee5e', $actual->getShopperKey());
        $this->assertEquals('ec11cd24-e667-4f9e-a677-5ffe0d4aee51', $actual->getAddressKey());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testHandleExceptionWhenCreatingShopperByOrder()
    {
        $this->expectException(LocalizedException::class);

        $this->clientMock->expects($this->exactly(1))->method('execute')->willThrowException(new RequestException(
            json_encode(['messages' => 'Property \'email\' must match \".+@.+\\..+\"']),
            new Request('GET', 'test'),
            new Response(400)
        ));

        $magentoOrder = $this->loadOrderById('100000001');
        $magentoOrder = $this->addCurrencyToOrder($magentoOrder);

        $this->expectExceptionMessage(
            //phpcs:ignore
            'The shopper by order address with ID "'. $magentoOrder->getBillingAddress()->getEntityId() .'" was not created properly.'
        );

        $this->shopperService->createByOrderAddress($magentoOrder->getBillingAddress());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testHandleExceptionWhenCreatingShopperByQuote()
    {
        $this->expectException(LocalizedException::class);

        $this->clientMock->expects($this->exactly(1))->method('execute')->willThrowException(new RequestException(
            json_encode(['messages' => 'Property \'email\' must match \".+@.+\\..+\"']),
            new Request('GET', 'test'),
            new Response(400)
        ));

        $magentoQuote = $this->loadQuoteById('test01');

        $this->expectExceptionMessage(
            //phpcs:ignore
            'The shopper by quote address with ID "'. $magentoQuote->getBillingAddress()->getId() .'" was not created properly.'
        );

        $this->shopperService->createByQuoteAddress($magentoQuote->getBillingAddress());
    }

    /**
     * Setup of test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ApiClientInterface::class);
        $this->setupShopperService();
    }

    /**
     * Setup of Order Service
     */
    private function setupShopperService()
    {
        $shopperClient = $this->objectManager->create(
            Shopper::class,
            [
                'apiClient' => $this->clientMock,
            ]
        );

        $this->shopperService = $this->objectManager->create(
            ShopperService::class,
            [
                'shopperClient' => $shopperClient,
            ]
        );
    }
}
