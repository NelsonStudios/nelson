<?php


namespace Fecon\SytelineIntegration\Plugin\Magento\Catalog\Model;

/**
 * Plugin to retrieve product prices from Syteline
 */
class ProductPrice
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
     * 
     * @param \Magento\Catalog\Model\Product $subject
     * @param float $result
     * @return float
     */
    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        $returnValue = $result;
        if ($this->sytelineHelper->existsInSyteline($subject)) {
            $returnValue = $this->sytelineHelper->getProductPrice($subject);
        }

        return $returnValue * 3;
    }
}