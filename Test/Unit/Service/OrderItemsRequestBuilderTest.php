<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Api\Service\OrderItemsRequestBuilderInterface;
use CM\Payments\Client\Model\Request\OrderItemCreate;
use CM\Payments\Client\Request\OrderItemsCreateRequest;
use CM\Payments\Service\Order\Item\Request\Part\Amount as OrderItemAmount;
use CM\Payments\Service\Order\Item\Request\Part\Currency as OrderItemCurrency;
use CM\Payments\Service\Order\Item\Request\Part\Description as OrderItemDescription;
use CM\Payments\Service\Order\Item\Request\Part\ItemId as OrderItemId;
use CM\Payments\Service\Order\Item\Request\Part\Name as OrderItemName;
use CM\Payments\Service\Order\Item\Request\Part\Quantity as OrderItemQuantity;
use CM\Payments\Service\Order\Item\Request\Part\Sku as OrderItemSku;
use CM\Payments\Service\Order\Item\Request\Part\Type as OrderItemType;
use CM\Payments\Service\Order\Item\Request\Part\UnitAmount as OrderItemUnitAmount;
use CM\Payments\Service\Order\Item\Request\Part\VatAmount as OrderItemVatAmount;
use CM\Payments\Service\Order\Item\Request\Part\VatRate as OrderItemVatRate;
use CM\Payments\Service\OrderItemsRequestBuilder;
use CM\Payments\Service\Quote\Item\Request\Part\Amount as QuoteItemAmount;
use CM\Payments\Service\Quote\Item\Request\Part\Currency as QuoteItemCurrency;
use CM\Payments\Service\Quote\Item\Request\Part\Description as QuoteItemDescription;
use CM\Payments\Service\Quote\Item\Request\Part\ItemId as QuoteItemId;
use CM\Payments\Service\Quote\Item\Request\Part\Name as QuoteItemName;
use CM\Payments\Service\Quote\Item\Request\Part\Quantity as QuoteItemQuantity;
use CM\Payments\Service\Quote\Item\Request\Part\Sku as QuoteItemSku;
use CM\Payments\Service\Quote\Item\Request\Part\Type as QuoteItemType;
use CM\Payments\Service\Quote\Item\Request\Part\UnitAmount as QuoteItemUnitAmount;
use CM\Payments\Service\Quote\Item\Request\Part\VatAmount as QuoteItemVatAmount;
use CM\Payments\Service\Quote\Item\Request\Part\VatRate as QuoteItemVatRate;
use CM\Payments\Test\Unit\UnitTestCase;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Framework\Exception\LocalizedException;

class OrderItemsRequestBuilderTest extends UnitTestCase
{
    /**
     * @var OrderItemsRequestBuilder
     */
    private $orderItemsRequestBuilder;

    /**
     * Test function of creation of order items based on order
     *
     * @throws LocalizedException
     */
    public function testCreateOrderItemsByOrderRequestBuilder()
    {
        $orderItems = $this->getOrderItems();
        $orderItemsRequest = $this->orderItemsRequestBuilder->create(
            '0287A1617D93780EF28044B98438BF2F',
            $orderItems
        );

        // Check the first (virtual) item
        $this->assertSame(1, $orderItemsRequest->getPayload()[0]['number']);
        $this->assertSame('virtual-item', $orderItemsRequest->getPayload()[0]['code']);
        $this->assertSame('Virtual Item Name', $orderItemsRequest->getPayload()[0]['name']);
        $this->assertSame('Virtual Item Description', $orderItemsRequest->getPayload()[0]['description']);
        $this->assertSame(
            OrderItemsRequestBuilderInterface::TYPE_DIGITAL,
            $orderItemsRequest->getPayload()[0]['type']
        );
        $this->assertSame(1, $orderItemsRequest->getPayload()[0]['quantity']);
        $this->assertSame('EUR', $orderItemsRequest->getPayload()[0]['currency']);
        $this->assertSame(8094, $orderItemsRequest->getPayload()[0]['unit_amount']);
        $this->assertSame(8094, $orderItemsRequest->getPayload()[0]['amount']);
        $this->assertSame(1405, $orderItemsRequest->getPayload()[0]['vat_amount']);
        $this->assertSame('21.0', $orderItemsRequest->getPayload()[0]['vat_rate']);

        // Check the second (physical) item
        $this->assertSame(2, $orderItemsRequest->getPayload()[1]['number']);
        $this->assertSame('physical-item', $orderItemsRequest->getPayload()[1]['code']);
        $this->assertSame('Physical Item Name', $orderItemsRequest->getPayload()[1]['name']);
        $this->assertSame('Physical Item Description', $orderItemsRequest->getPayload()[1]['description']);
        $this->assertSame(
            OrderItemsRequestBuilderInterface::TYPE_PHYSICAL,
            $orderItemsRequest->getPayload()[1]['type']
        );
        $this->assertSame(2, $orderItemsRequest->getPayload()[1]['quantity']);
        $this->assertSame('EUR', $orderItemsRequest->getPayload()[1]['currency']);
        $this->assertSame(6897, $orderItemsRequest->getPayload()[1]['unit_amount']);
        $this->assertSame(13794, $orderItemsRequest->getPayload()[1]['amount']);
        $this->assertSame(2394, $orderItemsRequest->getPayload()[1]['vat_amount']);
        $this->assertSame('21.0', $orderItemsRequest->getPayload()[1]['vat_rate']);
    }

    /**
     * Prepare the order items to mock objects
     *
     * @return array
     */
    protected function getOrderItems(): array
    {
        $orderMock = $this->getOrderMock();

        $orderItemVirtualMock = $this->createConfiguredMock(
            OrderItem::class,
            [
                'getItemId' => '1',
                'getSku' => 'virtual-item',
                'getName' => 'Virtual Item Name',
                'getDescription' => 'Virtual Item Description',
                'getIsVirtual' => '1',
                'getQtyOrdered' => '1',
                'getBaseRowTotal' => '68.9700',
                'getBaseDiscountAmount' => '0.0000',
                'getBaseTaxAmount' => '11.9700',
                'getBaseDiscountTaxCompensationAmount' => '0.0000',
                'getTaxPercent' => '21.0',
                'getOrder' => $orderMock,
                'setItemId' => $this->returnArgument(0)
            ]
        );

        $orderItemPhysicalMock = $this->createConfiguredMock(
            OrderItem::class,
            [
                'getItemId' => '2',
                'getSku' => 'physical-item',
                'getName' => 'Physical Item Name',
                'getDescription' => 'Physical Item Description',
                'getIsVirtual' => '0',
                'getQtyOrdered' => '2',
                'getBaseRowTotal' => '114.0000',
                'getBaseDiscountAmount' => '0.0000',
                'getBaseTaxAmount' => '23.9400',
                'getBaseDiscountTaxCompensationAmount' => '0.0000',
                'getTaxPercent' => '21.0',
                'getOrder' => $orderMock,
                'setItemId' => $this->returnArgument(0)
            ]
        );

        return [
            $orderItemVirtualMock,
            $orderItemPhysicalMock
        ];
    }

    /**
     * Prepare the order mock object
     *
     * @return Order
     */
    protected function getOrderMock(): Order
    {
        return $this->createConfiguredMock(
            Order::class,
            [
                'getBaseShippingAmount' => '15.0000',
                'getBaseDiscountAmount' => '0.0000',
                'getBaseShippingTaxAmount' => '0.0000',
                'getBaseShippingDiscountTaxCompensationAmnt' => '0.0000',
                'getOrderCurrencyCode' => 'EUR',
            ]
        );
    }

    /**
     * Test function of creation of order items based on quote
     *
     * @throws LocalizedException
     */
    public function testCreateOrderItemsByQuoteRequestBuilder()
    {
        $quoteItems = $this->getQuoteItems();
        $orderItemsRequest = $this->orderItemsRequestBuilder->createByQuoteItems(
            '0287A1617D93780EF28044B98438BF2F',
            $quoteItems
        );

        // Check the first (virtual) item
        $this->assertSame(1, $orderItemsRequest->getPayload()[0]['number']);
        $this->assertSame('virtual-item', $orderItemsRequest->getPayload()[0]['code']);
        $this->assertSame('Virtual Item Name', $orderItemsRequest->getPayload()[0]['name']);
        $this->assertSame('Virtual Item Description', $orderItemsRequest->getPayload()[0]['description']);
        $this->assertSame(
            OrderItemsRequestBuilderInterface::TYPE_DIGITAL,
            $orderItemsRequest->getPayload()[0]['type']
        );
        $this->assertSame(1, $orderItemsRequest->getPayload()[0]['quantity']);
        $this->assertSame('EUR', $orderItemsRequest->getPayload()[0]['currency']);
        $this->assertSame(8094, $orderItemsRequest->getPayload()[0]['unit_amount']);
        $this->assertSame(8094, $orderItemsRequest->getPayload()[0]['amount']);
        $this->assertSame(1405, $orderItemsRequest->getPayload()[0]['vat_amount']);
        $this->assertSame('21.0', $orderItemsRequest->getPayload()[0]['vat_rate']);

        // Check the second (physical) item
        $this->assertSame(2, $orderItemsRequest->getPayload()[1]['number']);
        $this->assertSame('physical-item', $orderItemsRequest->getPayload()[1]['code']);
        $this->assertSame('Physical Item Name', $orderItemsRequest->getPayload()[1]['name']);
        $this->assertSame('Physical Item Description', $orderItemsRequest->getPayload()[1]['description']);
        $this->assertSame(
            OrderItemsRequestBuilderInterface::TYPE_PHYSICAL,
            $orderItemsRequest->getPayload()[1]['type']
        );
        $this->assertSame(2, $orderItemsRequest->getPayload()[1]['quantity']);
        $this->assertSame('EUR', $orderItemsRequest->getPayload()[1]['currency']);
        $this->assertSame(6897, $orderItemsRequest->getPayload()[1]['unit_amount']);
        $this->assertSame(13794, $orderItemsRequest->getPayload()[1]['amount']);
        $this->assertSame(2394, $orderItemsRequest->getPayload()[1]['vat_amount']);
        $this->assertSame('21.0', $orderItemsRequest->getPayload()[1]['vat_rate']);
    }

    /**
     * Prepare the quote items to mock objects
     *
     * @return array
     */
    protected function getQuoteItems(): array
    {
        $quoteMock = $this->getQuoteMock();

        $quoteItemVirtualMock = $this->getMockBuilder(
            QuoteItem::class
        )->disableOriginalConstructor(
        )->onlyMethods(
            [
                'getItemId',
                'getSku',
                'getName',
                'getQty',
                'getQuote',
                'getPrice'
            ]
        )->addMethods(
            [
                'getDescription',
                'getIsVirtual',
                'getBaseRowTotal',
                'getBaseDiscountAmount',
                'getBaseTaxAmount',
                'getBaseDiscountTaxCompensationAmount',
                'getTaxPercent',
            ]
        )->getMock();
        $quoteItemVirtualMock->expects($this->any())->method('getItemId')->willReturn(1);
        $quoteItemVirtualMock->expects($this->any())->method('getSku')->willReturn('virtual-item');
        $quoteItemVirtualMock->expects($this->any())->method('getName')->willReturn('Virtual Item Name');
        $quoteItemVirtualMock->expects($this->any())->method('getDescription')
            ->willReturn('Virtual Item Description');
        $quoteItemVirtualMock->expects($this->any())->method('getIsVirtual')->willReturn(1);
        $quoteItemVirtualMock->expects($this->any())->method('getQty')->willReturn(1);
        $quoteItemVirtualMock->expects($this->any())->method('getBaseRowTotal')->willReturn('68.9700');
        $quoteItemVirtualMock->expects($this->any())->method('getBaseDiscountAmount')->willReturn('0.0000');
        $quoteItemVirtualMock->expects($this->any())->method('getBaseTaxAmount')->willReturn('11.9700');
        $quoteItemVirtualMock->expects($this->any())->method('getBaseDiscountTaxCompensationAmount')
            ->willReturn('0.0000');
        $quoteItemVirtualMock->expects($this->any())->method('getTaxPercent')->willReturn('21.0');
        $quoteItemVirtualMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);

        $quoteItemPhysicalMock = $this->getMockBuilder(
            QuoteItem::class
        )->disableOriginalConstructor(
        )->onlyMethods(
            [
                'getItemId',
                'getSku',
                'getName',
                'getQty',
                'getQuote',
                'getPrice'
            ]
        )->addMethods(
            [
                'getDescription',
                'getIsVirtual',
                'getBaseRowTotal',
                'getBaseDiscountAmount',
                'getBaseTaxAmount',
                'getBaseDiscountTaxCompensationAmount',
                'getTaxPercent',
            ]
        )->getMock();

        $quoteItemPhysicalMock->expects($this->any())->method('getItemId')->willReturn(2);
        $quoteItemPhysicalMock->expects($this->any())->method('getSku')->willReturn('physical-item');
        $quoteItemPhysicalMock->expects($this->any())->method('getName')->willReturn('Physical Item Name');
        $quoteItemPhysicalMock->expects($this->any())->method('getDescription')
            ->willReturn('Physical Item Description');
        $quoteItemPhysicalMock->expects($this->any())->method('getIsVirtual')->willReturn(0);
        $quoteItemPhysicalMock->expects($this->any())->method('getQty')->willReturn(2);
        $quoteItemPhysicalMock->expects($this->any())->method('getBaseRowTotal')->willReturn('114.0000');
        $quoteItemPhysicalMock->expects($this->any())->method('getBaseDiscountAmount')->willReturn('0.0000');
        $quoteItemPhysicalMock->expects($this->any())->method('getBaseTaxAmount')->willReturn('23.9400');
        $quoteItemPhysicalMock->expects($this->any())->method('getBaseDiscountTaxCompensationAmount')
            ->willReturn('0.0000');
        $quoteItemPhysicalMock->expects($this->any())->method('getTaxPercent')->willReturn('21.0');
        $quoteItemPhysicalMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);

        return [
            $quoteItemVirtualMock,
            $quoteItemPhysicalMock
        ];
    }

    /**
     * Prepare the quote to mock object
     *
     * @return Quote
     */
    protected function getQuoteMock(): Quote
    {
        $shippingAddressMock = $this->getMockBuilder(
            QuoteAddress::class,
        )->disableOriginalConstructor(
        )->addMethods(
            [
                'getBaseShippingAmount',
                'getBaseShippingTaxAmount',
                'getBaseShippingDiscountTaxCompensationAmnt'
            ]
        )->getMock();

        $shippingAddressMock->expects($this->any())
            ->method('getBaseShippingAmount')->willReturn('0.0000');
        $shippingAddressMock->expects($this->any())
            ->method('getBaseShippingTaxAmount')->willReturn('0.0000');
        $shippingAddressMock->expects($this->any())
            ->method('getBaseShippingDiscountTaxCompensationAmnt')->willReturn('0.0000');

        $quoteMock = $this->getMockBuilder(
            Quote::class,
        )->disableOriginalConstructor(
        )->onlyMethods(
            [
                'getIsVirtual',
                'getBillingAddress',
                'getShippingAddress'
            ]
        )->addMethods(
            [
                'getQuoteCurrencyCode'
            ]
        )->getMock();

        $quoteMock->expects($this->any())->method('getQuoteCurrencyCode')->willReturn('EUR');
        $quoteMock->expects($this->any())->method('getIsVirtual')->willReturn(false);
        $quoteMock->expects($this->any())->method('getBillingAddress')->willReturn($shippingAddressMock);
        $quoteMock->expects($this->any())->method('getShippingAddress')->willReturn($shippingAddressMock);

        return $quoteMock;
    }

    /**
     * Setup function
     */
    protected function setUp(): void
    {
        parent::setUp();

        $orderItemsFactoryMock = $this->getMockupFactory(OrderItemCreate::class);
        $orderItemsCreateRequestFactoryMock = $this->getMockupFactory(OrderItemsCreateRequest::class);

        $orderItemRequestParts = [
            new OrderItemId(),
            new OrderItemSku(),
            new OrderItemName(),
            new OrderItemDescription(),
            new OrderItemType(),
            new OrderItemQuantity(),
            new OrderItemCurrency(),
            new OrderItemAmount(),
            new OrderItemUnitAmount(),
            new OrderItemVatRate(),
            new OrderItemVatAmount()
        ];

        $quoteItemRequestParts = [
            new QuoteItemId(),
            new QuoteItemSku(),
            new QuoteItemName(),
            new QuoteItemDescription(),
            new QuoteItemType(),
            new QuoteItemQuantity(),
            new QuoteItemCurrency(),
            new QuoteItemAmount(),
            new QuoteItemUnitAmount(),
            new QuoteItemVatRate(),
            new QuoteItemVatAmount()
        ];

        $this->orderItemsRequestBuilder = new OrderItemsRequestBuilder(
            $orderItemsFactoryMock,
            $orderItemsCreateRequestFactoryMock,
            $orderItemRequestParts,
            $quoteItemRequestParts
        );
    }
}
