<?php

namespace Fecon\Sso\Block\Adminhtml\System\Config;

/**
 * Binding options for IsDefault configuration
 */
class IsDefault extends \Magento\Framework\View\Element\Html\Select
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
            __('0'), __('No')
        );
        $this->addOption(
            __('1'), __('Yes')
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