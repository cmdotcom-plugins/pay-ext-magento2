<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Service;

use CM\Payments\Api\Service\VersionServiceInterface;
use CM\Payments\Logger\CMPaymentsLogger;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Webapi\Rest\Request;

class VersionService implements VersionServiceInterface
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var CMPaymentsLogger
     */
    private $logger;

    /**
     * VersionService constructor
     *
     * @param ClientFactory $clientFactory
     * @param JsonSerializer $jsonSerializer
     * @param CMPaymentsLogger $logger
     */
    public function __construct(
        ClientFactory $clientFactory,
        JsonSerializer $jsonSerializer,
        CMPaymentsLogger $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function getLatestVersion(): string
    {
        $latestVersion = '';
        /** @var Client $client */
        $client = $this->clientFactory->create();
        try {
            /** @var Response $response */
            $response = $client->request(Request::HTTP_METHOD_GET, $this->getRepositoryUrl());

            $result = $this->jsonSerializer->unserialize(
                $response->getBody()->getContents()
            );

            if ($result['tag_name']) {
                $latestVersion = $result['tag_name'];
            }
        } catch (GuzzleException $exception) {
            $this->logger->info(
                'CM Get The Latest Version',
                [
                    'Status' => $exception->getCode(),
                    'Exception' => $exception->getMessage(),
                ]
            );
        } catch (InvalidArgumentException $exception) {
            $this->logger->info(
                'CM Get The Latest Version',
                [
                    'Status' => $exception->getCode(),
                    'Exception' => $exception->getMessage(),
                ]
            );
        }

        return $latestVersion;
    }

    /**
     * @inheritDoc
     */
    public function getRepositoryUrl(): string
    {
        return 'https://api.github.com/repos/' .
            self::REPOSITORY_VENDOR_NAME .
            '/' .
            self::REPOSITORY_EXTENSION_NAME .
            '/releases/latest';
    }
}
