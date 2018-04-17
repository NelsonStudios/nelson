<?php

namespace Serfe\SytelineIntegration\Helper;

/**
 * Helper to make SOAP API calls
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class SoapClient extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PRODUCTION_WSDL_CONFIG_PATH = "styline_integration/setting/wsdl_url_production";
    const TEST_WSDL_CONFIG_PATH = "styline_integration/setting/wsdl_url_test";
    const TEST_MODE_CONFIG_PATH = "styline_integration/setting/test_mode";
    const SOAP_VERSION = "styline_integration/setting/soap_version";
    
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
     * Executes a request to the SOAP API
     *
     * @param string $callable
     * @param mixed $data
     * @return mixed
     */
    protected function execRequest($callable, $data)
    {
        $wsdl = $this->getWsdl();
        $options = $this->getOptions();
        $client = new \Zend\Soap\Client($wsdl, $options);
        
        try {
            $response = $client->$callable($data);
        } catch (Exception $exc) {
            $response = $exc->getTraceAsString();
        }
            
        return $response;
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
    protected function getWsdl()
    {
        $testMode = $this->getConfigValue($this::TEST_MODE_CONFIG_PATH);
        $wsdlConfig = $this::PRODUCTION_WSDL_CONFIG_PATH;
        if ($testMode) {
            $wsdlConfig = $this::TEST_WSDL_CONFIG_PATH;
        }
        return $this->getConfigValue($wsdlConfig);
    }
    
    /**
     * Get options for the SOAP Client
     *
     * @return array
     */
    protected function getOptions()
    {
        $soapVersion = $this->getConfigValue($this::SOAP_VERSION) == 'SOAP_1_1'? SOAP_1_1 : SOAP_1_2;

        return [
            'soap_version' => $soapVersion
        ];
    }
}
