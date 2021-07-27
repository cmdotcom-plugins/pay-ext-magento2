<?php

namespace CM\Payments\Service\Method;

use CM\Payments\Api\Data\IssuerInterface;
use CM\Payments\Api\Data\IssuerInterfaceFactory;
use CM\Payments\Api\Data\PaymentMethodAdditionalDataInterface;
use CM\Payments\Api\Data\PaymentMethodAdditionalDataInterfaceFactory;
use CM\Payments\Api\Service\Method\ExtendMethodInterface;
use CM\Payments\Client\Model\Response\Method\IdealIssuer;
use CM\Payments\Client\Model\Response\PaymentMethod;
use CM\Payments\Model\ConfigProvider;
use Magento\Checkout\Api\Data\PaymentDetailsExtensionInterface;

class Ideal implements ExtendMethodInterface
{
    /**
     * @var PaymentMethodAdditionalDataInterfaceFactory
     */
    private $paymentMethodAdditionalDataFactory;
    /**
     * @var IssuerInterfaceFactory
     */
    private $issuerFactory;

    /**
     * Ideal constructor.
     * @param PaymentMethodAdditionalDataInterfaceFactory $paymentMethodAdditionalDataFactory
     * @param IssuerInterfaceFactory $issuerFactory
     */
    public function __construct(
        PaymentMethodAdditionalDataInterfaceFactory $paymentMethodAdditionalDataFactory,
        IssuerInterfaceFactory $issuerFactory
    ) {
        $this->paymentMethodAdditionalDataFactory = $paymentMethodAdditionalDataFactory;
        $this->issuerFactory = $issuerFactory;
    }

    /**
     * @inheritDoc
     */
    public function extend(
        string $paymentMethodCode,
        PaymentMethod $paymentMethod,
        PaymentDetailsExtensionInterface $paymentDetailsExtension
    ): PaymentDetailsExtensionInterface {
        if ($paymentMethodCode !== ConfigProvider::CODE_IDEAL || empty($paymentMethod->getIdealIssuers())) {
            return $paymentDetailsExtension;
        }

        $issuers = [];
        foreach ($this->sortIdealIssuers($paymentMethod->getIdealIssuers()) as $issuer) {
            /** @var IssuerInterface $issuerObject */
            $issuerObject = $this->issuerFactory->create();
            $issuerObject->setCode($issuer->getId());
            $issuerObject->setTitle($issuer->getName());
            $issuers[] = $issuerObject;
        }

        /** @var PaymentMethodAdditionalDataInterface $paymentMethodAdditionalData */
        $paymentMethodAdditionalData = $this->paymentMethodAdditionalDataFactory->create();
        $paymentMethodAdditionalData->setIssuers($issuers);
        $paymentDetailsExtension->setData($paymentMethodCode, $paymentMethodAdditionalData);

        return $paymentDetailsExtension;
    }

    /**
     * Sort ideal issuers asc by name
     *
     * @param IdealIssuer[] $issuerList
     * @return IdealIssuer[]
     */
    private function sortIdealIssuers(array $issuerList): array
    {
        usort($issuerList, function ($a, $b) {
            return strcmp($a->getName(), $b->getName());
        });

        return $issuerList;
    }
}
