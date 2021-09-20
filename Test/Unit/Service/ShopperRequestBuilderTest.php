<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Unit\Service;

use CM\Payments\Api\Service\ShopperRequestBuilderInterface;
use CM\Payments\Client\Model\Request\ShopperCreate;
use CM\Payments\Client\Request\ShopperCreateRequest;
use CM\Payments\Service\AddressService;
use CM\Payments\Service\Order\Address\Request\Part\Address as OrderAddressFull;
use CM\Payments\Service\Order\Address\Request\Part\DateOfBirth as OrderAddressDateOfBirth;
use CM\Payments\Service\Order\Address\Request\Part\Email as OrderAddressEmail;
use CM\Payments\Service\Order\Address\Request\Part\Gender as OrderAddressGender;
use CM\Payments\Service\Order\Address\Request\Part\Name as OrderAddressName;
use CM\Payments\Service\Order\Address\Request\Part\PhoneNumber as OrderAddressPhoneNumber;
use CM\Payments\Service\Order\Address\Request\Part\ShopperId as OrderAddressShopperId;
use CM\Payments\Service\Quote\Address\Request\Part\Address as QuoteAddressFull;
use CM\Payments\Service\Quote\Address\Request\Part\DateOfBirth as QuoteAddressDateOfBirth;
use CM\Payments\Service\Quote\Address\Request\Part\Email as QuoteAddressEmail;
use CM\Payments\Service\Quote\Address\Request\Part\Gender as QuoteAddressGender;
use CM\Payments\Service\Quote\Address\Request\Part\Name as QuoteAddressName;
use CM\Payments\Service\Quote\Address\Request\Part\PhoneNumber as QuoteAddressPhoneNumber;
use CM\Payments\Service\Quote\Address\Request\Part\ShopperId as QuoteAddressShopperId;
use CM\Payments\Service\ShopperRequestBuilder;
use CM\Payments\Test\Unit\UnitTestCase;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order as SalesOrder;
use PHPUnit\Framework\MockObject\MockObject;

class ShopperRequestBuilderTest extends UnitTestCase
{
    /**
     * @var ShopperRequestBuilderInterface
     */
    private $shopperRequestBuilder;

    /**
     * Test function of creation of shopper based on quote address
     *
     * @throws LocalizedException
     */
    public function testCreateShopperByQuoteAddressBuilder()
    {
        $quoteAddress = $this->getQuoteAddressMock();
        $shopperRequest = $this->shopperRequestBuilder->createByQuoteAddress(
            $quoteAddress
        );

        $this->assertSame(static::ADDRESS_DATA['email_address'], $shopperRequest->getPayload()['shopper_reference']);
        $this->assertSame(
            [
                'first'  => static::ADDRESS_DATA['firstname'],
                'middle' => static::ADDRESS_DATA['middlename'],
                'last'   => static::ADDRESS_DATA['lastname'],
            ],
            $shopperRequest->getPayload()['name']
        );
        $this->assertSame(
            [
                'street'      => "Boerhaavelaan",
                'housenumber' => "7",
                'postal_code' => preg_replace('/\s+/', '', static::ADDRESS_DATA['postal_code']),
                'city'        => static::ADDRESS_DATA['city'],
                'country'     => static::ADDRESS_DATA['country_code'],
            ],
            $shopperRequest->getPayload()['address']
        );
        $this->assertSame(static::ADDRESS_DATA['email_address'], $shopperRequest->getPayload()['email']);
        $this->assertSame(ShopperCreate::GENDER_UNKNOWN, $shopperRequest->getPayload()['gender']);
        $this->assertSame(static::ADDRESS_DATA['phone_number'], $shopperRequest->getPayload()['phone_number']);
    }

    /**
     * Test function of creation of shopper based on order address
     *
     * @throws LocalizedException
     */
    public function testCreateShopperByOrderAddressBuilder()
    {
        $orderAddress = $this->getOrderAddressMock();
        $shopperRequest = $this->shopperRequestBuilder->createByOrderAddress(
            $orderAddress
        );

        $this->assertSame(static::ADDRESS_DATA['email_address'], $shopperRequest->getPayload()['shopper_reference']);
        $this->assertSame(
            [
                'first'  => static::ADDRESS_DATA['firstname'],
                'middle' => static::ADDRESS_DATA['middlename'],
                'last'   => static::ADDRESS_DATA['lastname'],
            ],
            $shopperRequest->getPayload()['name']
        );
        $this->assertSame(
            [
                'street'      => "Boerhaavelaan",
                'housenumber' => "7",
                'postal_code' => preg_replace('/\s+/', '', static::ADDRESS_DATA['postal_code']),
                'city'        => static::ADDRESS_DATA['city'],
                'country'     => static::ADDRESS_DATA['country_code'],
            ],
            $shopperRequest->getPayload()['address']
        );
        $this->assertSame(static::ADDRESS_DATA['email_address'], $shopperRequest->getPayload()['email']);
        $this->assertSame(ShopperCreate::GENDER_UNKNOWN, $shopperRequest->getPayload()['gender']);
        $this->assertSame('1980-11-12', $shopperRequest->getPayload()['date_of_birth']);
        $this->assertSame(static::ADDRESS_DATA['phone_number'], $shopperRequest->getPayload()['phone_number']);
    }

    /**
     * Prepare the quote address mock object
     *
     * @return MockObject
     */
    protected function getQuoteAddressMock(): MockObject
    {
        return $this->createConfiguredMock(
            AddressInterface::class,
            [
                'getFirstname'  => static::ADDRESS_DATA['firstname'],
                'getMiddlename' => static::ADDRESS_DATA['middlename'],
                'getLastname'   => static::ADDRESS_DATA['lastname'],
                'getEmail'      => static::ADDRESS_DATA['email_address'],
                'getStreet'     => [static::ADDRESS_DATA['street_address1']],
                'getCity'       => static::ADDRESS_DATA['city'],
                'getRegionCode' => static::ADDRESS_DATA['region_code'],
                'getPostcode'   => static::ADDRESS_DATA['postal_code'],
                'getCompany'    => static::ADDRESS_DATA['company'],
                'getCountryId'  => static::ADDRESS_DATA['country_code'],
                'getTelephone'  => static::ADDRESS_DATA['phone_number'],
                'getCustomerId' => '',
            ]
        );
    }

    /**
     * Prepare the order address mock object
     *
     * @return MockObject
     */
    protected function getOrderAddressMock(): MockObject
    {
        $methodsMapping = [
            'getFirstname'  => static::ADDRESS_DATA['firstname'],
            'getMiddlename' => static::ADDRESS_DATA['middlename'],
            'getLastname'   => static::ADDRESS_DATA['lastname'],
            'getEmail'      => static::ADDRESS_DATA['email_address'],
            'getStreet'     => [static::ADDRESS_DATA['street_address1']],
            'getCity'       => static::ADDRESS_DATA['city'],
            'getRegionCode' => static::ADDRESS_DATA['region_code'],
            'getPostcode'   => static::ADDRESS_DATA['postal_code'],
            'getCompany'    => static::ADDRESS_DATA['company'],
            'getCountryId'  => static::ADDRESS_DATA['country_code'],
            'getTelephone'  => static::ADDRESS_DATA['phone_number'],
            'getCustomerId' => '',
            'getOrder'      => $this->getOrderMock(),
        ];

        $orderAddressMock = $this->getMockBuilder(
            OrderAddressInterface::class
        )->disableOriginalConstructor()->onlyMethods(
            [
                'getFirstname',
                'getMiddlename',
                'getLastname',
                'getEmail',
                'getStreet',
                'getCity',
                'getRegionCode',
                'getPostcode',
                'getCompany',
                'getCountryId',
                'getTelephone',
                'getCustomerId',
            ]
        )->addMethods(
            [
                'getOrder',
            ]
        )->getMockForAbstractClass();

        foreach ($methodsMapping as $method => $return) {
            $orderAddressMock->method($method)->willReturn($return);
        }

        return $orderAddressMock;
    }

    /**
     * Prepare the order mock object
     *
     * @return MockObject
     */
    protected function getOrderMock(): MockObject
    {
        $paymentMock = $this->getMockBuilder(OrderPaymentInterface::class)
            ->onlyMethods(['getAdditionalInformation', 'setAdditionalInformation'])
            ->getMockForAbstractClass();

        $paymentMock->method('getAdditionalInformation')->with('dob')
            ->willReturn('1980-11-12');
        $paymentMock->method('setAdditionalInformation')->willReturnSelf();

        $orderMock = $this->getMockBuilder(SalesOrder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->method('getPayment')->willReturn($paymentMock);

        return $orderMock;
    }

    /**
     * Setup function
     */
    protected function setUp(): void
    {
        parent::setUp();

        $shopperFactoryMock = $this->getMockupFactory(ShopperCreate::class);
        $shopperCreateRequestFactoryMock = $this->getMockupFactory(ShopperCreateRequest::class);

        $customerRepositoryMock = $this->getMockBuilder(CustomerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $addressServiceMock = new AddressService();

        $orderAddressRequestParts = [
            new OrderAddressShopperId(),
            new OrderAddressName(),
            new OrderAddressFull($addressServiceMock),
            new OrderAddressEmail(),
            new OrderAddressGender($customerRepositoryMock),
            new OrderAddressDateOfBirth($customerRepositoryMock),
            new OrderAddressPhoneNumber(),
        ];

        $quoteAddressRequestParts = [
            new QuoteAddressShopperId(),
            new QuoteAddressName(),
            new QuoteAddressFull($addressServiceMock),
            new QuoteAddressEmail(),
            new QuoteAddressGender($customerRepositoryMock),
            new QuoteAddressDateOfBirth($customerRepositoryMock),
            new QuoteAddressPhoneNumber(),
        ];

        $this->shopperRequestBuilder = new ShopperRequestBuilder(
            $shopperFactoryMock,
            $shopperCreateRequestFactoryMock,
            $orderAddressRequestParts,
            $quoteAddressRequestParts
        );
    }
}
