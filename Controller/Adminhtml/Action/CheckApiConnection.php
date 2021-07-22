<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Controller\Adminhtml\Action;

use CM\Payments\Api\Service\ApiTestServiceInterfaceFactory;
use CM\Payments\Api\Service\ApiTestServiceInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use GuzzleHttp\Exception\GuzzleException;

class CheckApiConnection extends Action
{
    /**
     * @var JsonResultFactory
     */
    private $jsonResultFactory;

    /**
     * @var ApiTestServiceInterfaceFactory
     */
    private $apiTestServiceFactory;

    /**
     * CheckApiConnection constructor
     *
     * @param Context $context
     * @param JsonResultFactory $jsonResultFactory
     * @param ApiTestServiceInterfaceFactory $apiTestServiceFactory
     */
    public function __construct(
        Context $context,
        JsonResultFactory $jsonResultFactory,
        ApiTestServiceInterfaceFactory $apiTestServiceFactory
    ) {
        parent::__construct($context);

        $this->jsonResultFactory = $jsonResultFactory;
        $this->apiTestServiceFactory = $apiTestServiceFactory;
    }

    /**
     * @return JsonResult
     */
    public function execute(): JsonResult
    {
        /** @var JsonResult $jsonResult */
        $jsonResult = $this->jsonResultFactory->create();

        $apiConnectionData = $this->getRequest()->getParams();
        $errors = $this->validateData($apiConnectionData);

        if (!$errors) {
            $success = true;

            try {
                /** @var ApiTestServiceInterface $apiTestService */
                $apiTestService = $this->apiTestServiceFactory->create(
                    [
                        'apiConnectionData' => [
                            'mode' => $apiConnectionData['mode'],
                            'merchantName' => $apiConnectionData['merchant_name'],
                            'merchantPassword' => $apiConnectionData['merchant_password'],
                            'merchantKey' => $apiConnectionData['merchant_key']
                        ]
                    ]
                );

                $result = $apiTestService->testApiConnection();

                if (!is_array($result)) {
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
            } catch (GuzzleException|NoSuchEntityException $e) {
                $data = [
                    'success' => false,
                    'connectionResult' => $e->getMessage()
                ];
            }
        } else {
            array_unshift($errors, __("The connection was unsuccessful."));
            $data = [
                'success' => false,
                'connectionResult' => implode('<br />', $errors)
            ];
        }

        return $jsonResult->setData(['result' => $data]);
    }

    /**
     * @param array $apiConnectionData
     * @return array
     */
    private function validateData(array $apiConnectionData): array
    {
        $errors = [];
        if (empty($apiConnectionData['mode'])) {
            $errors[] = __("The 'Api mode' was not recognized. Please, check the data.");
        }

        if (empty($apiConnectionData['merchant_name'])) {
            $errors[] = __("The 'Merchant Name' was not recognized. Please, check the data.");
        }

        if (empty($apiConnectionData['merchant_password'])) {
            $errors[] = __("The 'Merchant Password' was not recognized. Please, check the data.");
        }

        if (empty($apiConnectionData['merchant_key'])) {
            $errors[] = __("The 'Merchant Key' was not recognized. Please, check the data.");
        }

        return $errors;
    }
}
