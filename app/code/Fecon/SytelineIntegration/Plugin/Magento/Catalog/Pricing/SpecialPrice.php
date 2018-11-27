<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Fecon\SytelineIntegration\Plugin\Magento\Catalog\Pricing;

/**
 * Plugin to retrieve product's special prices from Syteline
 */
class SpecialPrice
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
     * Plugin to the getValue function to retrieve price from Syteline
     *
     * @param \Magento\Catalog\Pricing\Price\RegularPrice $subject
     * @param float $result
     * @return float
     */
    public function afterGetValue(\Magento\Catalog\Pricing\Price\SpecialPrice $subject, $result)
    {
        $returnValue = $result;
        $product = $subject->getProduct();
        $specialPrice = true;
        if ($this->sytelineHelper->existsInSyteline($product)) {
            $returnValue = $this->sytelineHelper->getProductPrice($product, $specialPrice);
        }

        return $returnValue;
    }
}