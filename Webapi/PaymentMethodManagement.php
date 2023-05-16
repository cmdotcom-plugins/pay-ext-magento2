<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Webapi;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\PaymentMethodManagementInterface;
use CM\Payments\Api\Service\MethodServiceInterface;
use CM\Payments\Client\Model\Response\Method\IdealIssuer;
use CM\Payments\Exception\PaymentMethodNotFoundException;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Model\PaymentDetailsFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface as CheckoutPaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;

class PaymentMethodManagement implements PaymentMethodManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var PaymentDetailsFactory
     */
    private $paymentDetailsFactory;

    /**
     * @var CheckoutPaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var CartTotalRepositoryInterface
     */
    private $cartTotalsRepository;

    /**
     * @var MethodServiceInterface
     */
    private $methodService;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $configService;

    /**
     * @param CheckoutPaymentMethodManagementInterface $paymentMethodManagement
     * @param PaymentDetailsFactory $paymentDetailsFactory
     * @param CartTotalRepositoryInterface $cartTotalsRepository
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CheckoutPaymentMethodManagementInterface $paymentMethodManagement,
        PaymentDetailsFactory $paymentDetailsFactory,
        CartTotalRepositoryInterface $cartTotalsRepository,
        CartRepositoryInterface $quoteRepository,
        MethodServiceInterface $methodService,
        ConfigInterface $configService
    ) {
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->quoteRepository = $quoteRepository;
        $this->methodService = $methodService;
        $this->configService = $configService;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaymentMethods(
        int $cartId,
        AddressInterface $shippingAddress = null
    ): PaymentDetailsInterface {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $this->validateQuote($quote);
        $this->validateAddress($shippingAddress);

        /** @var PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));

        return $paymentDetails;
    }

    /**
     * {@inheritDoc}
     */
    public function getIbanIssuers(int $cartId): array
    {
        if (!$this->configService->isAvailablePaymentMethodsCheckEnabled()) {
            $issuers = [];

            foreach (MethodServiceInterface::IDEAL_ISSUERS as $issuer) {
                $issuers[] = new IdealIssuer($issuer);
            }

            return $issuers;
        }

        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $this->validateQuote($quote);

        try {
            $cmMethods = $this->methodService->getCmMethods($quote->getCmOrderKey());
            $idealMethod = $this->methodService->getMethodFromList(MethodServiceInterface::CM_METHOD_IDEAL, $cmMethods);

            return $idealMethod->getIdealIssuers();
        } catch (PaymentMethodNotFoundException $exception) {
            throw new InputException(
                __('Ideal method not found in the list')
            );
        }
    }

    /**
     * Validate quote
     *
     * @param Quote $quote
     * @return void
     * @throws InputException
     */
    protected function validateQuote(Quote $quote): void
    {
        if (!$quote->getItemsCount()) {
            throw new InputException(
                __('The shipping method can\'t be set for an empty cart. Add an item to cart and try again.')
            );
        }
    }

    /**
     * Validate shipping address
     *
     * @param AddressInterface|null $address
     * @return void
     * @throws StateException
     */
    private function validateAddress(?AddressInterface $address): void
    {
        if (!$address || !$address->getCountryId()) {
            throw new StateException(__('The shipping address is missing. Set the address and try again.'));
        }
    }
}
