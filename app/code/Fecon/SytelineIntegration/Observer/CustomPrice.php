<?php

namespace Fecon\SytelineIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Observer to pull de real price from Syteline when a product is added to the cart
 */
class CustomPrice implements ObserverInterface
{

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
     * Execture function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
        $product = $observer->getEvent()->getData('product');
        if ($this->sytelineHelper->existsInSyteline($product)) {
            $price = $this->sytelineHelper->getProductPrice($product);
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
        }
    }
}