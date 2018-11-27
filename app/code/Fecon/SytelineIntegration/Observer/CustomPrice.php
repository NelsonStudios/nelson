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
            $price = $this->getSytelineFinalPrice($product);
            if ($price !== false) {
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
            }
        }
    }

    /**
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @return float|boolean    Returns false if the API respond with error
     */
    protected function getSytelineFinalPrice($product)
    {
        $finalPrice = false;
        $forceAPI = true;
        $regularPrice = $this->sytelineHelper->getProductPrice($product, false, $forceAPI);
        $specialPrice = $this->sytelineHelper->getProductPrice($product, true, $forceAPI);
        if ($regularPrice !== false &&
            $specialPrice !== false
        ) {
            $finalPrice = ($specialPrice < $regularPrice) ? $specialPrice : $regularPrice;
        } elseif ($regularPrice !== false) {
            $finalPrice = $regularPrice;
        }

        return $finalPrice;
    }
}