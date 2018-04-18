<?php

namespace Serfe\SytelineIntegration\Helper;

/**
 * Wrapper for SoapClient Helper
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class ApiHelper extends SoapClient
{
    /**
     * Data Handler
     *
     * @var \Serfe\SytelineIntegration\Helper\DataHandler 
     */
    protected $dataHandler;

    /**
     * Logger
     *
     * @var \Serfe\SytelineIntegration\Logger\Handler 
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeMaganger
     * @param \Serfe\SytelineIntegration\Helper\DataHandler $dataHandler
     * @param \Serfe\SytelineIntegration\Logger\Handler $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeMaganger,
        \Serfe\SytelineIntegration\Helper\DataHandler $dataHandler,
        \Serfe\SytelineIntegration\Logger\Logger $logger
    ) {
        $this->dataHandler = $dataHandler;
        $this->logger = $logger;

        parent::__construct($context, $storeMaganger);
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
            $response = ['error'];
        }
        

        return $response;
    }

    /**
     * Call the GetCart Web Service
     *
     * @param arrau $data
     * @return mixed
     */
    public function getCart($data)
    {
        if ($this->dataHandler->isValidGetCartData($data, $errors)) {
            $cardData = $this->dataHandler->parseCartData($data);
            $response = $this->execRequest("GetCart", $cardData);
        } else {
            $response = ['errors' => $errors];
            foreach ($errors as $error) {
                $this->logger->err($error);
            }
        }

        return $response;
    }

    /**
     * Returns the 
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
