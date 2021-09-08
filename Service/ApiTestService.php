<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Service\ApiTestServiceInterface;
use CM\Payments\Client\Api\OrderInterface;
use CM\Payments\Api\Config\ConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class ApiTestService implements ApiTestServiceInterface
{
    /**
     * @var array
     */
    private $apiConnectionData;

    /**
     * @var ConfigInterface
     */
    private $config;
    /**
     * @var OrderInterface
     */
    private $orderClient;

    /**
     * ApiTestService constructor
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        OrderInterface $orderClient
    ) {
        $this->config = $config;
        $this->orderClient = $orderClient;

        try {
            $this->apiConnectionData = [
                'mode' => $this->config->getMode(),
                'merchantName' => $this->config->getMerchantName(),
                'merchantPassword' => $this->config->getMerchantPassword(),
                'merchantKey' => $this->config->getMerchantKey()
            ];
        } catch (NoSuchEntityException $e) {
            $this->apiConnectionData = [];
        }
    }

    /**
     * @inheritDoc
     */
    public function testApiConnection(): array
    {
        $resultData = ['errors' => [], 'result' => null];

        if (!empty($this->apiConnectionData)) {
            $errors = $this->validateData();
            $resultData['errors'] = $errors;

            if (empty($errors)) {
                $resultData['result'] = $this->orderClient->getList(date('Y-m-d'));
            }
        }

        return $resultData;
    }

    /**
     * @return array
     */
    private function validateData(): array
    {
        $errors = [];
        if (empty($this->apiConnectionData['mode'])) {
            $errors[] = __("The 'Api mode' was not recognized. Please, check the data.");
        }

        if (empty($this->apiConnectionData['merchantName'])) {
            $errors[] = __("The 'Merchant Name' was not recognized. Please, check the data.");
        }

        if (empty($this->apiConnectionData['merchantPassword'])) {
            $errors[] = __("The 'Merchant Password' was not recognized. Please, check the data.");
        }

        if (empty($this->apiConnectionData['merchantKey'])) {
            $errors[] = __("The 'Merchant Key' was not recognized. Please, check the data.");
        }

        return $errors;
    }
}
