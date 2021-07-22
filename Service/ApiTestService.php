<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Service\ApiTestServiceInterface;
use CM\Payments\Client\Api\RequestInterface;
use CM\Payments\Client\ApiClient;
use CM\Payments\Model\Adminhtml\Source\Mode;
use GuzzleHttp\Client as HttpClient;

class ApiTestService implements ApiTestServiceInterface
{
    /**
     * @var array
     */
    private $apiConnectionData;

    /**
     * ApiTestService constructor
     *
     * @param array $apiConnectionData
     */
    public function __construct(
        array $apiConnectionData
    ) {
        $this->apiConnectionData = $apiConnectionData;
    }

    /**
     * @inheritDoc
     */
    public function testApiConnection(): array
    {
        $options = [
            'json' => ['date' => date('Y-m-d')]
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
            $this->apiConnectionData['merchantName'] .
                ':' . $this->apiConnectionData['merchantPassword']
        );
        return new HttpClient(
            [
                'base_uri' => $baseApiUrl,
                'headers' => [
                    'Authorization' => $authorizationToken
                ]
            ]
        );
    }
}
