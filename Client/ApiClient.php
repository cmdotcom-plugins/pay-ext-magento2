<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Client\Api\ApiClientInterface;
use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Logger\CMPaymentsLogger;
use CM\Payments\Model\Adminhtml\Source\Mode;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\NoSuchEntityException;

class ApiClient implements ApiClientInterface
{
    /**
     * API urls
     */
    public const API_TEST_URL = 'https://testsecure.docdatapayments.com/ps/api/public/v1/merchants/';
    public const API_URL = 'https://secure.docdatapayments.com/ps/api/public/v1/merchants/';
    public const THIS_IS_JUST_A_TEST = 'test';

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * ApiClient constructor.
     *
     * @param CMPaymentsLogger $logger
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        CMPaymentsLogger $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Execute a request against the Payments API.
     *
     * @param RequestInterface $request
     * @return array
     * @throws GuzzleException|NoSuchEntityException
     */
    public function execute(RequestInterface $request): array
    {
        $options = [];
        if ($request->getPayload()) {
            $options = [
                'json' => $request->getPayload()
            ];
        }

        $guzzleResponse = $this->getClient()->request(
            $request->getRequestMethod(),
            $request->getEndpoint(),
            $options
        );

        $response = $guzzleResponse->getBody()->getContents();
        if (!$response) {
            $response = [];
        } else {
            $response = \GuzzleHttp\json_decode($response, true);
        }

        if ($this->config->isLogAllRestApiCalls()) {
            $this->logger->debug(
                'CM REST request:',
                [
                    'method' => $request->getRequestMethod(),
                    'endpoint' => $request->getEndpoint(),
                    'options' => \json_encode($options, JSON_PRETTY_PRINT),
                    'response' => \json_encode($response, JSON_PRETTY_PRINT)
                ]
            );
        }

        return $response;
    }

    /**
     * @return HttpClient
     * @throws NoSuchEntityException
     */
    private function getClient(): HttpClient
    {
        if (!$this->httpClient) {
            $merchantName = $this->config->getMerchantName();
            $merchantPassword = $this->config->getMerchantPassword();

            $authorizationToken = 'Basic ' . base64_encode($merchantName . ':' . $merchantPassword);
            $this->httpClient = new HttpClient(
                [
                    'base_uri' => $this->getBaseApiUrl(),
                    'headers' => [
                        'Authorization' => $authorizationToken
                    ]
                ]
            );
        }

        return $this->httpClient;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function getBaseApiUrl(): string
    {
        $url = $this->config->getMode() === Mode::LIVE ? self::API_URL : self::API_TEST_URL;

        return $url . $this->config->getMerchantKey() . '/';
    }
}
