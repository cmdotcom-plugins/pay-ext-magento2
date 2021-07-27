<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Adminhtml\Action;

use CM\Payments\Api\Service\ApiTestServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class CheckApiConnection extends Action
{
    /**
     * @var JsonResultFactory
     */
    private $jsonResultFactory;

    /**
     * @var ApiTestServiceInterface
     */
    private $apiTestService;

    /**
     * CheckApiConnection constructor
     *
     * @param Context $context
     * @param JsonResultFactory $jsonResultFactory
     * @param ApiTestServiceInterface $apiTestService
     */
    public function __construct(
        Context $context,
        JsonResultFactory $jsonResultFactory,
        ApiTestServiceInterface $apiTestService
    ) {
        parent::__construct($context);

        $this->jsonResultFactory = $jsonResultFactory;
        $this->apiTestService = $apiTestService;
    }

    /**
     * @return JsonResult
     */
    public function execute(): JsonResult
    {
        /** @var JsonResult $jsonResult */
        $jsonResult = $this->jsonResultFactory->create();
        $success = true;

        try {
            $resultData = $this->apiTestService->testApiConnection();

            if (!empty($resultData['errors'])) {
                array_unshift($resultData['errors'], __("The connection was unsuccessful."));
                $data = [
                    'success' => false,
                    'connectionResult' => implode('<br />', $resultData['errors'])
                ];
            } elseif (!is_array($resultData['result'])) {
                $data = [
                    'success' => false,
                    'connectionResult' => __("The connection was unsuccessful. Please, check your credentials.")
                ];
            } else {
                $data = [
                    'success' => $success,
                    'connectionResult' => __("The connection was successful. Your credentials are valid.")
                ];
            }
        } catch (GuzzleException | NoSuchEntityException $e) {
            $data = [
                'success' => false,
                'connectionResult' => $e->getMessage()
            ];
        }

        return $jsonResult->setData(['result' => $data]);
    }
}
