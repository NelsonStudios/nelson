<?php

namespace Fecon\Sso\Block\Adminhtml\System\Config;

/**
 * Endpoint block
 */
class Endpoint extends \Magento\Framework\View\Element\Html\Select
{

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $this->_options = [];

        $this->addOption(
            __('AssertionConsumerService'), __('AssertionConsumerService')
        );
        $this->addOption(
            __('SingleLogoutService'), __('SingleLogoutService')
        );

        return parent::_toHtml();
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}