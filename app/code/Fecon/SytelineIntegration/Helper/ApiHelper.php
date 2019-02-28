<?php

namespace Fecon\SytelineIntegration\Helper;

/**
 * Wrapper for SoapClient Helper
 *
 * 
 */
class ApiHelper extends SoapClient
{
    /**
     * Data Handler
     *
     * @var \Fecon\SytelineIntegration\Helper\DataHandler 
     */
    protected $dataHandler;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Fecon\SytelineIntegration\Helper\ConfigHelper $configHelper
     * @param \Fecon\SytelineIntegration\Helper\DataHandler $dataHandler
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Fecon\SytelineIntegration\Helper\ConfigHelper $configHelper,
        \Fecon\SytelineIntegration\Helper\DataHandler $dataHandler
    ) {
        $this->dataHandler = $dataHandler;

        parent::__construct($context, $configHelper);
    }

    /**
     * Get Part Info
     *
     * @param string $partNumber
     * @param string $qty
     * @param string $customerId
     * @return mixed
     */
    public function getPartInfo($data)
    {
        if ($this->dataHandler->isValidPartData($data, $errors)) {
            $partInfo = $this->dataHandler->parsePartData($data);
            $response = $this->execRequest("GetPartInfo", $partInfo);
        } else {
            $response = ['errors' => $errors];
        }
        

        return $response;
    }

    /**
     * Call the GetCart Web Service
     *
     * @param array $data
     * @return mixed
     */
    public function getCart($data)
    {
        if ($this->dataHandler->isValidGetCartData($data, $errors)) {
            $cardData = $this->dataHandler->parseCartData($data);
            $response = $this->execRequest("GetCart", $cardData);
        } else {
            $response = ['errors' => $errors];
        }

        return $response;
    }

    /**
     * Call the GetAddresses Web Service
     *
     * @param array $data
     * @return mixed
     */
    public function getAddresses($data)
    {
        if ($this->dataHandler->isValidGetAddressesData($data, $errors)) {
            $getAddressesData = $this->dataHandler->parseGetAddressesData($data);
            $response = $this->execRequest("GetAddresses", $getAddressesData);
        } else {
            $response = ['errors' => $errors];
        }

        return $response;
    }

    /**
     * Returns the Soap Types
     *
     * @return mixed
     */
    public function getSoapTypes()
    {
        $wsdl = $this->getWsdl();
        $options = $this->getOptions();
        $client = new \Zend\Soap\Client($wsdl, $options);

        return $client->getTypes();
    }
}
