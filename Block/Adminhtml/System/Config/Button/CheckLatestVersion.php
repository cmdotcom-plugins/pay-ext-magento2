<?php
/**
 * Copyright © CM.com. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace CM\Payments\Block\Adminhtml\System\Config\Button;

use CM\Payments\Api\Config\ConfigInterface;
use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\NoSuchEntityException;

class CheckLatestVersion extends Field
{
    /**
     * @var string
     */
    protected $_template = 'CM_Payments::system/config/button/check_latest_version.phtml';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * CheckLatestVersion constructor
     *
     * @param Context $context
     * @param ConfigInterface $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
    }

    /**
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getCurrentVersion(): ?string
    {
        return $this->config->getCurrentVersion();
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getLatestVersionCheckUrl(): string
    {
        return $this->getUrl('cmpayments/action/getLatestVersion');
    }

    /**
     * @return string
     */
    public function getButtonHtml(): string
    {
        $buttonData = ['id' => 'cmpayments_button_version', 'label' => __('Check the Latest Version')];
        try {
            $button = $this->getLayout()->createBlock(
                Button::class
            )->setData($buttonData);

            return $button->toHtml();
        } catch (Exception $e) {
            return '';
        }
    }
}
