<?php

namespace Serfe\StylineIntegration\Helper;

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
    
    protected function getWsdl()
    {
        $testMode = $this->scopeConfig->getValue($this::TEST_MODE_CONFIG_PATH);
        $wsdlConfig = $this::PRODUCTION_WSDL_CONFIG_PATH;
        if ($testMode) {
            $wsdlConfig = $this::TEST_WSDL_CONFIG_PATH;
        }
        return $this->scopeConfig->getValue($wsdlConfig);
    }
    
    protected function getOptions()
    {
        return [
            'soap_version' => SOAP_1_1
        ];
    }
    
    public function getPartInfo($partNumber, $qty, $customerId)
    {
        $partInfo = new \stdClass();
        $partInfo->ErpGetPartInfoRequest = new \stdClass();
        $partInfo->ErpGetPartInfoRequest->PartNumber = $partNumber;
        $partInfo->ErpGetPartInfoRequest->Quantity = $qty;
        $partInfo->ErpGetPartInfoRequest->CustomerId = $customerId;

        return $this->execRequest("GetPartInfo",$partInfo);
    }
}
