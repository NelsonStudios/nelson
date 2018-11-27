<?php


namespace Fecon\SytelineIntegration\Plugin\Magento\Catalog\Pricing;

/**
 * Plugin to retrieve product prices from Syteline
 */
class RegularPrice
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
     * @param \Magento\Catalog\Pricing\Price\RegularPrice $subject
     * @param float $result
     * @return float
     */
    public function afterGetValue(\Magento\Catalog\Pricing\Price\RegularPrice $subject, $result)
    {
        $returnValue = $result;
        $product = $subject->getProduct();
        if ($this->sytelineHelper->existsInSyteline($product)) {
            $sytelinePrice = $this->sytelineHelper->getProductPrice($product);
            if ($sytelinePrice !== false) {
                $returnValue = $sytelinePrice;
            }
        }

        return $returnValue;
    }
}