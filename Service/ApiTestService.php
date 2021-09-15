<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Service\ApiTestServiceInterface;
use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Client\ApiClient;
use CM\Payments\Model\Adminhtml\Source\Mode;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\NoSuchEntityException;

class ApiTestService implements ApiTestServiceInterface
{
    /**
     * Encrypted value placeholder
     */
    public const ENCRYPTED_VALUE_PLACEHOLDER = '******';

    /**
     * @var array
     */
    private $apiConnectionData;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * ApiTestService constructor
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;

        try {
            $this->apiConnectionData = [
                'mode'             => $this->config->getMode(),
                'merchantName'     => $this->config->getMerchantName(),
                'merchantPassword' => $this->config->getMerchantPassword(),
                'merchantKey'      => $this->config->getMerchantKey(),
            ];
        } catch (NoSuchEntityException $e) {
            $this->apiConnectionData = [];
        }
    }

    /**
     * @inheritDoc
     */
    public function testApiConnection(array $merchantData): array
    {
        $resultData = ['errors' => [], 'result' => null];
        $this->prepareData($merchantData);

        if (!empty($this->apiConnectionData)) {
            $errors = $this->validateData();
            $resultData['errors'] = $errors;

            if (empty($errors)) {
                try {
                    $resultData['result'] = $this->getLatestOrders(date('Y-m-d'));
                } catch (GuzzleException $e) {
                    $resultData['errors'][] = $e->getMessage();
                }
            }
        }

        return $resultData;
    }

    /**
     * @param array $merchantData
     * @return void
     */
    private function prepareData(array $merchantData): void
    {
        foreach ($merchantData as $property => $propertyValue) {
            if ($propertyValue !== self::ENCRYPTED_VALUE_PLACEHOLDER) {
                $this->apiConnectionData[$property] = $propertyValue;
            }
        }
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

    /**
     * @param string $date
     * @return array
     * @throws GuzzleException
     */
    private function getLatestOrders(string $date): array
    {
        $options = [
            'json' => ['date' => $date],
        ];

        $guzzleResponse = $this->getClient()->request(
            RequestInterface::HTTP_GET,
            'orders',
            $options
        );

        return \GuzzleHttp\json_decode($guzzleResponse->getBody()->getContents(), true);
    }

    /**
     * @return HttpClient
     */
    private function getClient(): HttpClient
    {
        $baseApiUrl = $this->apiConnectionData['mode'] === Mode::LIVE ? ApiClient::API_URL : ApiClient::API_TEST_URL;
        $baseApiUrl .= $this->apiConnectionData['merchantKey'] . '/';

        $authorizationToken = 'Basic ' . base64_encode(
            $this->apiConnectionData['merchantName'] . ':' . $this->apiConnectionData['merchantPassword']
        );

        return new HttpClient(
            [
                'base_uri' => $baseApiUrl,
                'headers'  => [
                    'Authorization' => $authorizationToken,
                ],
            ]
        );
    }
}
