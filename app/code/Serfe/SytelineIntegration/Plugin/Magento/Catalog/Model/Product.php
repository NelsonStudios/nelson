<?php

namespace Serfe\SytelineIntegration\Plugin\Magento\Catalog\Model;

/**
 * Plugin to detect if a product is Available based on Syteline Web Service
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class Product
{
    /**
     * Syteline  Helper
     *
     * @var \Serfe\SytelineIntegration\Helper\SytelineHelper 
     */
    protected $sytelineHelper;

    /**
     * Constructor
     *
     * @param \Serfe\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
     */
    public function __construct(
        \Serfe\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
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
}
