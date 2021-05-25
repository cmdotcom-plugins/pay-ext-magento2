<?php
/**
 * Copyright © 2021 cm.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Client;

use CM\Payments\Api\Client\ApiClientInterface;
use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Client\Api\RequestInterface;
use GuzzleHttp\Client as HttpClient;

class ApiClient implements ApiClientInterface
{
    const API_URL = 'https://testsecure.docdatapayments.com/ps/api/public/v1/merchants/';

    /**
     * @var HttpClient
     */
    private $httpClient;
    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Execute a request against the Payments API.
     *
     * @param RequestInterface $request
     * @return array
     */
    public function execute(RequestInterface $request): array
    {
        $guzzleResponse = $this->getClient()->request(
            $request->getRequestMethod(),
            $request->getEndpoint(),
            [
                'json' => $request->getPayload()
            ]
        );

        return \GuzzleHttp\json_decode($guzzleResponse->getBody()->getContents(), true);
    }

    /**
     * @return HttpClient
     */
    private function getClient(): HttpClient
    {
        if (!$this->httpClient) {
            $merchantName = $this->config->getMerchantName();
            $merchantPassword = $this->config->getMerchantPassword();

            $authorizationToken = 'Basic ' . base64_encode($merchantName . ':' . $merchantPassword);
            $this->httpClient = new HttpClient([
                'base_uri' => static::API_URL . $this->config->getMerchantKey() . '/',
                'headers' => [
                    'Authorization' => $authorizationToken
                ]
            ]);
        }

        return $this->httpClient;
    }
}
