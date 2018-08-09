<?php

namespace Fecon\SytelineIntegration\Helper;

/**
 * Helper to make SOAP API calls
 *
 * 
 */
class SoapClient extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Config Helper
     *
     * @var \Fecon\SytelineIntegration\Helper\ConfigHelper
     */
    protected $configHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Fecon\SytelineIntegration\Helper\ConfigHelper $configHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Fecon\SytelineIntegration\Helper\ConfigHelper $configHelper
    ) {
        $this->configHelper = $configHelper;
        
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
        $wsdl = $this->configHelper->getWsdl();
        $options = $this->getOptions();
        $client = new \Zend\Soap\Client($wsdl, $options);

        try {
            $response = $client->$callable($data);
        } catch (\SoapFault $exc) {
            $response = $this->processSoapFault($exc);
        }

        return $response;
    }

    /**
     * Process SoapFault attributes
     *
     * @param \SoapFault $soapFault
     * @return array
     */
    protected function processSoapFault($soapFault)
    {
        $error = [
            'errors' => [
                'type' => 'SoapFault',
                'code' => $soapFault->faultcode,
                'message' => $soapFault->getMessage()
            ]
        ];
        
        return $error;
    }

    /**
     * Get options for the SOAP Client
     *
     * @return array
     */
    protected function getOptions()
    {
        $soapVersion = $this->configHelper->getSoapVersion();

        return [
            'soap_version' => $soapVersion
        ];
    }
}
