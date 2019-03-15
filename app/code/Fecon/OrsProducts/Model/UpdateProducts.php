<?php

namespace Fecon\OrsProducts\Model;

use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Class to update products attributes
 */
class UpdateProducts
{

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Get attribute set ID by name
     *
     * @param string $attributeSetName
     * @return string|int
     */
    protected function getAttributeSetId($attributeSetName)
    {
        $attributeSetCollection = $this->attributeCollectionFactory->create();
        $attributeSetCollection
            ->addFieldToFilter('entity_type_id',4)
            ->addFieldToFilter('attribute_set_name',$attributeSetName);
        $attributeSet = current($attributeSetCollection->getData());
        $attributeSetId = $attributeSet["attribute_set_id"];

        return $attributeSetId;
    }

    /**
     * Get product collection filtered by attribute set
     *
     * @param string|int $attributeSetId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getProductionCollection($attributeSetId)
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addFieldToSelect("name")
            ->addFieldToFilter('attribute_set_id',$attributeSetId);

        return $productCollection;
    }

    /**
     * Update ORS products visibility
     *
     * @param OutputInterface $output
     * @return void
     */
    public function updateProductsVisibility(OutputInterface $output)
    {
        $attributeSetName = 'ORS Products';
        $attributeSetId = $this->getAttributeSetId($attributeSetName);
        $productCollection = $this->getProductionCollection($attributeSetId);
        foreach ($productCollection as $product) {
            $loadedProduct = $this->getProduct($product->getId());
            if ($loadedProduct) {
                $this->updateProductVisibility($output, $loadedProduct);
            }
        }
    }

    /**
     * Load product by id
     *
     * @param string|int $productId
     * @return ProductInterface|null
     */
    protected function getProduct($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Exception $ex) {
            $product = null;
        }

        return $product;
    }

    /**
     * Update product visibility
     *
     * @param OutputInterface $output
     * @param ProductInterface $product
     */
    protected function updateProductVisibility(OutputInterface $output, ProductInterface $product)
    {
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $websiteIds = $product->getWebsiteIds();
        if (is_array($websiteIds) || !in_array(1, $websiteIds)) {
            $websiteIds = [1];
        }
        $product->setWebsiteIds($websiteIds);
        $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
        $stockItem->setManageStock(0);
        $stockItem->setUseConfigManageStock(0);
        $stockItem->setIsInStock(1);
        $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
        try {
            $this->productRepository->save($product);
            $output->writeln("Product updated " . $product->getName());
        } catch (\Exception $ex) {
            $output->writeln("Failed to update product " . $product->getName());
            $output->writeln($ex->getMessage());
        }
    }
}