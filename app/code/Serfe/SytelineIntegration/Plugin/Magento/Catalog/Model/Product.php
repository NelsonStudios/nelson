<?php

namespace Serfe\SytelineIntegration\Plugin\Magento\Catalog\Model;

/**
 * Plugin to detect if a product is Available based on Syteline Web Service
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class Product
{
    protected $sytelineHelper;

    public function __construct(
        \Serfe\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
    ) {
        $this->sytelineHelper = $sytelineHelper;
    }

    public function afterIsAvailable(
        \Magento\Catalog\Model\Product $subject,
        $result
    ) {
        return $this->sytelineHelper->isProductAvailable($subject);
    }
}
