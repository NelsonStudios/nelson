<?php

namespace Fecon\OrsProducts\Model;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of SkuPrefix
 */
class SkuPrefix extends UpdateProducts
{

    /**
     * Update ORS products visibility
     *
     * @param OutputInterface $output
     * @param array $csvData
     * @return void
     */
    public function updateSkus(OutputInterface $output)
    {
        $attributeSetName = 'ORS Products';
        $attributeSetId = $this->getAttributeSetId($attributeSetName);
        $productCollection = $this->getProductionCollection($attributeSetId);
        $productsUpdated = 0;
        foreach ($productCollection as $product) {
            $sku = $product->getSku();
            $skuToSearch = $sku;
            $prefix = 'EC-';
            if (strpos($sku, $prefix) !== 0) {
                $skuToSearch = substr($sku, strlen($prefix));
                try {
                    $this->updateProductSku($product->getId());
                    $productsUpdated++;
                    $message = 'Product ' . $product->getName() . ' updated';
                    $output->writeln("<info>" . $message . "</info>");
                } catch (\Exception $ex) {
                    $message = 'Cannot update product ' . $product->getName() . ', error message: ' . $ex->getMessage();
                    $output->writeln("<error>" . $message . "</error>");
                }
            }
        }
        $output->writeln("\n\n<info>Job Finished: " . $productsUpdated . " products were updated</info>");
    }

    protected function updateProductSku($productId)
    {
        $product = $this->productRepository->getById($productId);
        $newSku = "EC-" . $product->getSku();
        $product->setSku($newSku);
        $this->productRepository->save($product);
    }
}