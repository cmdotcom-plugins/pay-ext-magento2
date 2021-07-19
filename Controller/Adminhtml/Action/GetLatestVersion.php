<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Adminhtml\Action;

use CM\Payments\Api\Config\ConfigInterface;
use CM\Payments\Api\Service\VersionServiceInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class GetLatestVersion extends Action
{
    /**
     * @var JsonResultFactory
     */
    private $jsonResultFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var VersionServiceInterface
     */
    private $versionService;

    /**
     * GetLatestVersion constructor
     *
     * @param Context $context
     * @param JsonResultFactory $jsonResultFactory
     * @param ConfigInterface $config
     * @param VersionServiceInterface $versionService
     */
    public function __construct(
        Context $context,
        JsonResultFactory $jsonResultFactory,
        ConfigInterface $config,
        VersionServiceInterface $versionService
    ) {
        parent::__construct($context);

        $this->jsonResultFactory = $jsonResultFactory;
        $this->config = $config;
        $this->versionService = $versionService;
    }

    /**
     * @return JsonResult
     * @throws NoSuchEntityException
     */
    public function execute(): JsonResult
    {
        /** @var JsonResult $jsonResult */
        $jsonResult = $this->jsonResultFactory->create();

        $latestVersion = $this->versionService->getLatestVersion();
        $currentVersion = $this->config->getCurrentVersion();
        if (!$latestVersion) {
            $latestVersion = $currentVersion;
        }

        $data = [
            'currentVersion' => $currentVersion,
            'latestVersion' => $latestVersion
        ];

        return $jsonResult->setData(['result' => $data]);
    }
}
