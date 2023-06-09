<?php

namespace Fecon\SytelineIntegration\Helper;

/**
 * Helper for module configurations
 *
 * 
 */
class ConfigHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PRODUCTION_WSDL_CONFIG_PATH = "syteline_integration/setting/wsdl_url_production";
    const TEST_WSDL_CONFIG_PATH = "syteline_integration/setting/wsdl_url_test";
    const TEST_MODE_CONFIG_PATH = "syteline_integration/setting/test_mode";
    const SOAP_VERSION = "syteline_integration/setting/soap_version";
    const ADMIN_EMAIL = "syteline_integration/setting/email";
    const DEFAULT_CUSTOEMR_ID = "syteline_integration/setting/guest_customer_syteline_id";

    /**
     * Store Manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface 
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeMaganger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeMaganger
    ) {
        $this->storeManager = $storeMaganger;
        
        parent::__construct($context);
    }

    /**
     * Get configured value for current website
     *
     * @param string $configPath
     * @return mixed
     */
    protected function getConfigValue($configPath)
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        return $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $websiteCode);
    }

    /**
     * Get WSDL URL
     *
     * @return string
     */
    public function getWsdl()
    {
        $wsdlConfig = $this::PRODUCTION_WSDL_CONFIG_PATH;
        if ($this->isTestModeEnabled()) {
            $wsdlConfig = $this::TEST_WSDL_CONFIG_PATH;
        }
        return $this->getConfigValue($wsdlConfig);
    }

    /**
     * Get the SOAP Version
     *
     * @return array
     */
    public function getSoapVersion()
    {
        $soapVersion = $this->getConfigValue($this::SOAP_VERSION) == 'SOAP_1_1'? SOAP_1_1 : SOAP_1_2;

        return $soapVersion;
    }

    /**
     * Returns true if testing mode configuration is enabled
     *
     * @return bool
     */
    public function isTestModeEnabled()
    {
        return (bool) $this->getConfigValue($this::TEST_MODE_CONFIG_PATH);
    }

    /**
     * Returns configured admin email
     *
     * @return string
     */
    public function getAdminEmail()
    {
        $adminEmail = $this->getConfigValue($this::ADMIN_EMAIL);

        return $adminEmail;
    }

    /**
     * Get default Sytiline Customer Id
     *
     * @return string
     */
    public function getDefaultSytelineCustomerId()
    {
        $defaultId = $this->getConfigValue($this::DEFAULT_CUSTOEMR_ID);

        return $defaultId;
    }
}
