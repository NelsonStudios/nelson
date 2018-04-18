<?php

namespace Serfe\SytelineIntegration\Helper;

/**
 * Main Syteline Helper
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class SytelineHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SYTELINE_CUSTOMER_ID = 'C000037';
    const SYTELINE_AVAIALABLE_STATUS = 'Available';

    /**
     *
     * @var \Serfe\SytelineIntegration\Helper\ApiHelper 
     */
    protected $apiHelper;

    /**
     * Logger
     *
     * @var \Serfe\SytelineIntegration\Logger\Handler 
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Serfe\SytelineIntegration\Helper\ApiHelper $apiHelper,
        \Serfe\SytelineIntegration\Logger\Logger $logger
    ) {
        $this->apiHelper = $apiHelper;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * Check in Sytline if $product is available
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $qty
     * @return boolean
     */
    public function isProductAvailable(\Magento\Catalog\Model\Product $product, $qty = '1')
    {
        $productData = $this->productToArray($product, $qty);
        $apiResponse = $this->apiHelper->getPartInfo($productData);
        $available = $this->getAvailability($apiResponse);

        return $available;
    }

    /**
     * Generate array data to send via GetPartInfo Web Service
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $qty
     * @return array
     */
    protected function productToArray(\Magento\Catalog\Model\Product $product, $qty)
    {
        return [
            "PartNumber" => $product->getSku(), //**IMPORTANT**: Change getSku for getPartNumber
            "Quantity" => $qty,
            "CustomerId" => $this::SYTELINE_CUSTOMER_ID
        ];
    }

    /**
     * Get availability attribute from API Response
     *
     * @param array|\stdClass $response
     * @return boolean
     */
    protected function getAvailability($response)
    {
        $available = false;
        if (is_array($response)) {
            // DO LOGGING
        } else {
            if (!$this->responseHasErrors($response)) {
                $available = ($response->ErpGetPartInfoResponse->Availability == $this::SYTELINE_AVAIALABLE_STATUS);
            }
        }

        return $available;
    }

    /**
     * Check if Syteline API Response has errors
     *
     * @param \stdClass $response
     * @return boolean
     */
    protected function responseHasErrors($response)
    {
        $hasErrors = false;
        if (isset($response->ErpGetPartInfoResponse)) {
            if (!isset($response->ErpGetPartInfoResponse->Availability)) {
                $hasErrors = true;
                //LOG Web Service Error
            }
        } elseif (isset($response->SubmitCartResponse)) {
            if (!$response->SubmitCartResponse->Success) {
                $hasErrors = true;
                //LOG Web Service Error
            }
        }

        return $hasErrors;
    }

    /**
     * Log data errors
     *
     * @param array $response
     */
    protected function logDataErrors($response)
    {
        foreach ($response['errors'] as $error) {
            $this->logger->err($error);
        }
    }
}
