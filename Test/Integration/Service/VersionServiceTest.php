<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Test\Integration\Service;

use CM\Payments\Client\Model\CMPaymentUrlFactory;
use CM\Payments\Service\VersionService;
use CM\Payments\Test\Integration\IntegrationTestCase;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\ClientFactory;
use InvalidArgumentException;

class VersionServiceTest extends IntegrationTestCase
{
    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     */
    public function testGetLatestVersion()
    {
        $clientFactoryMock = $this->createMock(ClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())->method('request')->willReturn(new Response(
            200,
            [],
            json_encode([
                "tag_name" => "v0.1.0"
            ])
        ));
        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);
        $versionService = $this->objectManager->create(
            VersionService::class,
            [
                'clientFactory' => $clientFactoryMock
            ]
        );

        $actual = $versionService->getLatestVersion();

        $this->assertEquals('v0.1.0', $actual);
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     */
    public function testEmptyTagName()
    {
        $clientFactoryMock = $this->createMock(ClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $clientMock->expects($this->once())->method('request')->willReturn(new Response(
            200,
            [],
            json_encode([
                "tag_name" => ""
            ])
        ));
        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);
        $versionService = $this->objectManager->create(
            VersionService::class,
            [
                'clientFactory' => $clientFactoryMock
            ]
        );

        $actual = $versionService->getLatestVersion();

        $this->assertEquals('', $actual);
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     */
    public function testThrowGuzzleClientException()
    {
        $clientFactoryMock = $this->createMock(ClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $clientMock
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new ClientException('error', new Request('GET', 'test'), new Response(500)));

        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);
        $versionService = $this->objectManager->create(
            VersionService::class,
            [
                'clientFactory' => $clientFactoryMock
            ]
        );

        $actual = $versionService->getLatestVersion();

        $this->assertEquals('', $actual);
    }

    /**
     * @magentoConfigFixture default_store cm_payments/general/enabled 1
     */
    public function testThrowInvalidArgumentsException()
    {
        $clientFactoryMock = $this->createMock(ClientFactory::class);
        $clientMock = $this->createMock(Client::class);
        $clientMock
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new InvalidArgumentException());

        $clientFactoryMock->expects($this->once())->method('create')->willReturn($clientMock);
        $versionService = $this->objectManager->create(
            VersionService::class,
            [
                'clientFactory' => $clientFactoryMock
            ]
        );

        $actual = $versionService->getLatestVersion();

        $this->assertEquals('', $actual);
    }
}
