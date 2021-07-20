<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace CM\Payments\Gateway\Validator;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class CurrencyValidator extends AbstractValidator
{
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    private $config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param \Magento\Payment\Gateway\ConfigInterface $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config
    ) {
        $this->config = $config;
        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $storeId = $validationSubject['storeId'];

        if ((int)$this->config->getValue('allow_specific_currency', $storeId) !== 1) {
            return $this->createResult(true);
        }

        $availableCurrencies = $this->getAvailableCurrencies($storeId);
        if (!empty($availableCurrencies) && !in_array($validationSubject['currency'], $availableCurrencies)) {
            return $this->createResult(false);
        }

        return $this->createResult(true);
    }

    /**
     * @param string $storeId
     *
     * @return string[]
     */
    private function getAvailableCurrencies(string $storeId): array
    {
        $specificCurrency = $this->config->getValue('specific_currency', $storeId);
        if (empty($specificCurrency)) {
            return [];
        }

        return explode(',', $specificCurrency);
    }
}
