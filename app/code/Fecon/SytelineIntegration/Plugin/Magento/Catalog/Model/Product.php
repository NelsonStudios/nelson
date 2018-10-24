<?php

namespace Fecon\SytelineIntegration\Plugin\Magento\Catalog\Model;

/**
 * Plugin to detect if a product is Available based on Syteline Web Service
 *
 * 
 */
class Product
{
    /**
     * Syteline  Helper
     *
     * @var \Fecon\SytelineIntegration\Helper\SytelineHelper 
     */
    protected $sytelineHelper;

    /**
     * Constructor
     *
     * @param \Fecon\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
     */
    public function __construct(
        \Fecon\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
    ) {
        $this->sytelineHelper = $sytelineHelper;
    }

    /**
     * isAvailable Plugin
     *
     * @param \Magento\Catalog\Model\Product $subject
     * @param boolean $result
     * @return boolean
     */
    public function afterIsAvailable(
        \Magento\Catalog\Model\Product $subject,
        $result
    ) {
        $returnValue = $result;
        if ($this->sytelineHelper->existsInSyteline($subject)) {
            $returnValue = $this->sytelineHelper->isProductAvailable($subject);
        }

        return $returnValue;
    }

    /**
     * 
     * @param \Magento\Catalog\Model\Product $subject
     * @param float $result
     * @return float
     */
    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        $returnValue = $result;
//        if ($this->sytelineHelper->existsInSyteline($subject)) {
//            $returnValue = $this->sytelineHelper->getProductPrice($subject);
//        }

        return $returnValue;
    }
}
