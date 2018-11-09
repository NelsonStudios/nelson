<?php

namespace Fecon\Sso\Helper;

/**
 * Config Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    const SSO_ENABLED_PATH = 'sso/settings/enabled';
    const SSO_SSL_PRIVATE_KEY_PATH = 'sso/settings/ssl_private_key';
    const SSO_SSL_CERTIFICATE_PATH = 'sso/settings/ssl_certificate';

    /**
     * Check if SSO is enabled
     *
     * @return boolean
     */
    public function isSsoEnabled()
    {
        return (bool) $this->scopeConfig->getValue(self::SSO_ENABLED_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get SSL Private Key path
     *
     * @return string
     */
    public function getSslPrivateKey()
    {
        return $this->scopeConfig->getValue(self::SSO_SSL_PRIVATE_KEY_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get SSL Certificate path
     *
     * @return string
     */
    public function getSslCertificate()
    {
        return $this->scopeConfig->getValue(self::SSO_SSL_CERTIFICATE_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }
}