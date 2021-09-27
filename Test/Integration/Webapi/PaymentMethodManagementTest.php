<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Integration\Webapi;

use CM\Payments\Api\Model\Data\OrderInterfaceFactory;
use CM\Payments\Api\Model\Domain\CMOrderInterfaceFactory;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\ApiClient;
use CM\Payments\Test\Integration\IntegrationTestCase;
use CM\Payments\Webapi\PaymentMethodManagement;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;

class PaymentMethodManagementTest extends IntegrationTestCase
{
    /**
     * @var ApiClientInterface|MockObject
     */
    private $clientMock;

    /** @var PaymentMethodManagement */
    private $paymentMethodManagement;

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 0
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 0
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 0
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetPaymentsNoItemsInQuote()
    {
        $cartRepository = $this->objectManager->create(CartItemRepositoryInterface::class);
        $this->clientMock->expects($this->never())->method('execute');

        $magentoQuote = $this->loadQuoteById('test01');

        foreach ($magentoQuote->getItems() as $item) {
            $cartRepository->deleteById($magentoQuote->getId(), $item->getItemId());
        }
        $this->expectException(InputException::class);
        $this->expectExceptionMessage(
            'The shipping method can\'t be set for an empty cart. Add an item to cart and try again.'
        );
        $this->paymentMethodManagement->getPaymentMethods((int)$magentoQuote->getId());
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 0
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 0
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 0
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetPaymentsEmptyShippingAddress()
    {
        $this->clientMock->expects($this->never())->method('execute');

        $magentoQuote = $this->loadQuoteById('test01');

        $this->expectException(StateException::class);
        $this->expectExceptionMessage('The shipping address is missing. Set the address and try again.');
        $this->paymentMethodManagement->getPaymentMethods((int)$magentoQuote->getId());
    }

    /**
     * @magentoConfigFixture default_store payment/checkmo/active 0
     * @magentoConfigFixture default_store payment/fake/active 0
     * @magentoConfigFixture default_store payment/fake_vault/active 0
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     * @magentoConfigFixture default_store payment/cm_payments/active 0
     * @magentoConfigFixture default_store payment/cm_payments_creditcard/active 0
     * @magentoConfigFixture default_store payment/cm_payments_ideal/active 1
     * @magentoConfigFixture default_store payment/cm_payments_paypal/active 0
     * @magentoConfigFixture default_store payment/cm_payments_bancontact/active 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    public function testGetPayments()
    {
        $magentoQuote = $this->loadQuoteById('test01');

        $response = $this->paymentMethodManagement
            ->getPaymentMethods((int)$magentoQuote->getId(), $magentoQuote->getBillingAddress());

        $this->assertEquals('cm_payments_ideal', $response->getPaymentMethods()[0]->getCode());
        $this->assertEquals('cm_payments_bancontact', $response->getPaymentMethods()[1]->getCode());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ApiClientInterface::class);
        $this->paymentMethodManagement = $this->objectManager->create(PaymentMethodManagement::class);
        $this->objectManager->addSharedInstance($this->clientMock, ApiClient::class);
    }
}
