<?php

namespace Fecon\SytelineIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $sytelineHelper = $objectManager->get('\Fecon\SytelineIntegration\Helper\SytelineHelper');
        $dataTransformHelper = $objectManager->get('\Fecon\SytelineIntegration\Helper\TransformData');
        $apiHelper = $objectManager->get('\Fecon\SytelineIntegration\Helper\ApiHelper');
        
        if ($sytelineHelper->existsInSyteline($product)) {
            $productData = $dataTransformHelper->productToArray($product, 1);
            $apiResponse = $apiHelper->getPartInfo($productData);
            $newQty = $sytelineHelper->extractQtyFromResponse($apiResponse, $product->getId());
            $sku = $product->getSku();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $stockRegistry = $objectManager->create('Magento\CatalogInventory\Api\StockRegistryInterface');
            
            $stockItem = $stockRegistry->getStockItemBySku($sku);
            $stockItem->setQty($newQty);
            $stockItem->setIsInStock(true);
            $stockRegistry->updateStockItemBySku($sku, $stockItem);
            
            
            /*$stockItem = $product->getExtensionAttributes()->getStockItem();
             if($stockItem) {
             $stockData = $stockItem->getQty();
             $stockItem->setQty($newQty); //Set New Qty to Main Qty
             $stockItem->setIsInStock(true);
             $stockItem->save();
             }*/
        }
    }
}