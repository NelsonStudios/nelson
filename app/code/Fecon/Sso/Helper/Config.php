<?php

namespace Fecon\Sso\Helper;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Config Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    const SSO_ENABLED_PATH = 'sso/settings/enabled';
    const SSO_SSL_PRIVATE_KEY_PATH = 'sso/settings/ssl_private_key';
    const SSO_SSL_CERTIFICATE_PATH = 'sso/settings/ssl_certificate';
    const SSO_SP_ENTITY_ID_PATH = 'sso/sp_settings/sp_entity_id';
    const SSO_SP_ENDPOINTS_PATH = 'sso/sp_settings/endpoints';
    const SSO_SP_NAME_FORMAT_ID_PATH = 'sso/sp_settings/sp_name_format_id';
    const SSO_SP_PUBLIC_CERTIFICATE_PATH = 'sso/sp_settings/sp_public_certificate';
    const SSO_SP_VALIDATE_AUTHN_REQ_PATH = 'sso/sp_settings/validate_authnrequest';
    const SSO_SP_SAML20_SIGN_ASRT_PATH = 'sso/sp_settings/saml20_sign_assertion';

    /**
    * @var SerializerInterface
    */
    protected $serializer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->serializer = $serializer;
    }

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
     * Get SP Entity ID
     *
     * @return string
     */
    public function getSpEntityId()
    {
        return $this->scopeConfig->getValue(self::SSO_SP_ENTITY_ID_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
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

    /**
     * Get SP Endpoints
     *
     * @return string
     */
    public function getSpEndpoints()
    {
        $serializedData = $this->scopeConfig->getValue(self::SSO_SP_ENDPOINTS_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
        $data = $this->serializer->unserialize($serializedData);
        return $data;
    }

    /**
     * Get SP NameFormatID
     *
     * @return string
     */
    public function getSpNameFormatId()
    {
        return $this->scopeConfig->getValue(self::SSO_SP_NAME_FORMAT_ID_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get SP Public Certificate
     *
     * @return string
     */
    public function getSpPublicCertificate()
    {
        return $this->scopeConfig->getValue(self::SSO_SP_PUBLIC_CERTIFICATE_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get SP Validate Authn Request
     *
     * @return string
     */
    public function getSpValidateAuthnReq()
    {
        return (bool) $this->scopeConfig->getValue(self::SSO_SP_VALIDATE_AUTHN_REQ_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get SSL Certificate path
     *
     * @return string
     */
    public function getSpSamlSignAssertion()
    {
        return (bool) $this->scopeConfig->getValue(self::SSO_SP_SAML20_SIGN_ASRT_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);
    }
}