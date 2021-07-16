<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Mollie\Payment\Controller\Adminhtml\Action;

use CM\Payments\Api\Config\ConfigInterface;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class GetLatestVersion extends Action
{
    /**
     * @var JsonResultFactory
     */
    private $jsonResultFactory;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * GetLatestVersion constructor
     *
     * @param Context $context
     * @param JsonResultFactory $jsonResultFactory
     * @param JsonSerializer $jsonSerializer
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        JsonResultFactory $jsonResultFactory,
        JsonSerializer $jsonSerializer,
        ConfigInterface $config
    ) {
        parent::__construct($context);

        $this->jsonResultFactory = $jsonResultFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->config = $config;
    }

    /**
     * @return JsonResult
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $jsonResult = $this->jsonResultFactory->create();
        $result = $this->getLatestVersion();
        $current = $latest = $this->config->getCurrentVersion();
        if ($result) {
            $data = $this->jsonSerializer->unserialize($result);
            $versions = array_keys($data);
            $latest = reset($versions);
            foreach ($data as $version => $changes) {
                if ('v' . $version == $current) {
                    break;
                }
            }
        }

        $data = [
            'current_version' => $current,
            'last_version' => $latest
        ];

        return $jsonResult->setData(['result' => $data]);
    }

    private function getLatestVersion()
    {
        try {
            return [];
        } catch (Exception $exception) {
            return '';
        }
    }
}
