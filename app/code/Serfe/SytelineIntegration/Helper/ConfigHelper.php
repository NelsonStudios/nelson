<?php

namespace Serfe\SytelineIntegration\Helper;

/**
 * Helper for module configurations
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class ConfigHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PRODUCTION_WSDL_CONFIG_PATH = "syteline_integration/setting/wsdl_url_production";
    const TEST_WSDL_CONFIG_PATH = "syteline_integration/setting/wsdl_url_test";
    const TEST_MODE_CONFIG_PATH = "syteline_integration/setting/test_mode";
    const SOAP_VERSION = "syteline_integration/setting/soap_version";

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
        $testMode = $this->getConfigValue($this::TEST_MODE_CONFIG_PATH);
        $wsdlConfig = $this::PRODUCTION_WSDL_CONFIG_PATH;
        if ($testMode) {
            $wsdlConfig = $this::TEST_WSDL_CONFIG_PATH;
        }
        return $this->getConfigValue($wsdlConfig);
    }

    /**
     * Get the SOAP Version
     *
     * @return array
     */
    protected function getSoapVersion()
    {
        $soapVersion = $this->getConfigValue($this::SOAP_VERSION) == 'SOAP_1_1'? SOAP_1_1 : SOAP_1_2;

        return $soapVersion;
    }
}
