<?php

namespace Fecon\OrsProducts\Model;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of DeleteProducts
 */
class DeleteProducts extends UpdateProducts
{

    /**
     * @var string[]
     */
    protected $skus;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
        parent::__construct($attributeCollectionFactory, $productCollectionFactory, $productRepository, $stockRegistry);
    }
    /**
     * Update ORS products visibility
     *
     * @param OutputInterface $output
     * @param array $csvData
     * @return void
     */
    public function deleteProducts(OutputInterface $output, $csvData)
    {
        $attributeSetName = 'ORS Products';
        $attributeSetId = $this->getAttributeSetId($attributeSetName);
        $productCollection = $this->getProductionCollection($attributeSetId);
        $this->initliazeSkus($csvData);
        $productsDeleted = 0;
        foreach ($productCollection as $product) {
            $sku = $product->getSku();
            $skuToSearch = $sku;
            $prefix = 'EC-';
            if (strpos($sku, $prefix) === 0) {
                $skuToSearch = substr($sku, strlen($prefix));
            }
            if (!in_array($skuToSearch, $this->skus)) {
                try {
                    $this->productRepository->deleteById($sku);
                    $productsDeleted++;
                    $message = 'Product ' . $product->getName() . ' deleted';
                    $output->writeln("<info>" . $message . "</info>");
                } catch (\Exception $ex) {
                    $message = 'Cannot delete product ' . $product->getName() . ', error message: ' . $ex->getMessage();
                    $output->writeln("<error>" . $message . "</error>");
                }
            }
        }
        $output->writeln("\n\n<info>Job Finished: " . $productsDeleted . " products were deleted</info>");
    }

    protected function initliazeSkus($csvData)
    {
        foreach ($csvData as $row) {
            $this->skus[] = $row[0];
        }
    }
}