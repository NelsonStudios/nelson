<?php

namespace Fecon\Sso\Block\Adminhtml\System\Config;

/**
 * Binding options for Endpoints configuration
 */
class Binding extends \Magento\Framework\View\Element\Html\Select
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
            __('urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'), __('HTTP-POST')
        );
        $this->addOption(
            __('urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'), __('HTTP-Redirect')
        );
        $this->addOption(
            __('urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact'), __('HTTP-Artifact')
        );
        $this->addOption(
            __('urn:oasis:names:tc:SAML:2.0:bindings:PAOS'), __('PAOS')
        );
        $this->addOption(
            __('urn:oasis:names:tc:SAML:2.0:profiles:holder-of-key:SSO:browser'), __('SSO:browser')
        );
        $this->addOption(
            __('urn:oasis:names:tc:SAML:2.0:bindings:SOAP'), __('SOAP')
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