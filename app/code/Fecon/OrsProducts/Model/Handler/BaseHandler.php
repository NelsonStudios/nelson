<?php

namespace Fecon\OrsProducts\Model\Handler;

/**
 * Base Handler
 */
class BaseHandler implements \Fecon\OrsProducts\Api\HandlerInterface
{

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var array
     */
    protected $customAttributes = [];

    /**
     * @var array
     */
    protected $attributesToUpdate = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Website\Link
     */
    protected $productWebsiteLink;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var \Fecon\OrsProducts\Helper\CategoryHelper
     */
    protected $categoryHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Fecon\OrsProducts\Helper\AttributeHelper
     */
    protected $attributeHelper;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Website\Link $productWebsiteLink
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement
     * @param \Fecon\OrsProducts\Helper\CategoryHelper $categoryHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Website\Link $productWebsiteLink,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Fecon\OrsProducts\Helper\CategoryHelper $categoryHelper,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Fecon\OrsProducts\Helper\AttributeHelper $attributeHelper
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->productWebsiteLink = $productWebsiteLink;
        $this->stockRegistry = $stockRegistry;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->categoryHelper = $categoryHelper;
        $this->productResource = $productResource;
        $this->attributeHelper = $attributeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function processData($row, &$message = '')
    {
        $message = 'success';
        return true;
    }

    /**
     * 
     * @param array $rawData
     * @param string $message
     */
    public function createProduct($rawData, &$message = '')
    {
        $product = $this->productFactory->create();
        $sku = $this->getAttributeValue('sku', $rawData);
        $name = $this->getAttributeValue('name', $rawData);
        $price = $this->getAttributeValue('price', $rawData);
        $weight = $this->getAttributeValue('weight', $rawData);
        $attributeSetId = $this->getAttributeSetId();
        $product->setSku($sku);
        $product->setName($name);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $product->setPrice($price);
        $product->setAttributeSetId($attributeSetId);
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $product->setWeight($weight);
        $product = $this->setCustomAttributes($product, $rawData);

        try {
            $product = $this->productRepository->save($product);
            $success = true;
            $message = 'Product ' . $product->getName() . ' created';
        } catch (\Exception $ex) {
            $success = false;
            $message = $ex->getMessage();
        }

        return $success;
    }

    /**
     * Update $product data
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $rawData
     * @param string $message
     * @return boolean
     */
    public function updateProduct($product, $rawData, &$message = '')
    {
        $success = true;
        try {
            foreach ($this->attributesToUpdate as $attribute) {
                $value = $this->getAttributeValue($attribute, $rawData);
                $product->setCustomAttribute($attribute, $value);
                $this->productResource->saveAttribute($product, $attribute);
            }
            $this->assignProductToCategories($product, $rawData);
            $message = 'Product ' . $product->getName() . ' updated';
        } catch (\Exception $ex) {
            $success = false;
            $message = $ex->getMessage();
        }

        return $success;
    }

    /**
     * Extract $attribute value from $data
     *
     * @param string $attribute
     * @param array $data
     * @return string
     */
    protected function getAttributeValue($attribute, $data)
    {
        $position = $this->configuration[$attribute]['position'];

        $rawValue = utf8_encode(trim($data[$position]));

        if ($this->configuration[$attribute]['type'] == self::TYPE_STRING) {
            $value = $rawValue;
        } elseif ($this->configuration[$attribute]['type'] == self::TYPE_HTML) {
            $value = htmlspecialchars($rawValue);
        } elseif ($this->configuration[$attribute]['type'] == self::TYPE_SELECT) {
            $value = $this->attributeHelper->createOrGetId($attribute, $rawValue);
        }

        return $rawValue;
    }

    /**
     * Get the default attribute set that will be assigned to new products
     *
     * @return int
     */
    public function getAttributeSetId()
    {
        return 4;   // Default attribute set id
    }

    /**
     * Set configured custom attributes to $product
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $rawData
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    protected function setCustomAttributes($product, $rawData)
    {
        foreach ($this->customAttributes as $customAttribute) {
            $value = $this->getAttributeValue($customAttribute, $rawData);
            $product->setCustomAttribute($customAttribute, $value);
        }

        return $product;
    }

    /**
     * Assign product to website
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     */
    protected function assignProductToWebsite($product)
    {
        $websiteIds = $this->getWebsiteIds();
        $this->productWebsiteLink->saveWebsiteIds($product, $websiteIds);
    }

    /**
     * Get the default websites that will be assigned to new products
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        return [1];
    }

    /**
     * Update $product stock
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $rawData
     */
    protected function updateProductStock($product, $rawData)
    {
        $manageStock = $this->getAttributeValue('manage_stock', $rawData);
        $qty = $this->getAttributeValue('qty', $rawData);
        $isInStock = $this->getAttributeValue('is_in_stock', $rawData);
        $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
        $stockItem->setManageStock($manageStock);
        $stockItem->setUseConfigManageStock(0);
        $stockItem->setIsInStock($isInStock);
        $stockItem->setQty($qty);
        $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
    }

    /**
     * Assign $product to categories
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $rawData
     */
    protected function assignProductToCategories($product, $rawData)
    {
        $categoriesIds = $this->getCategoriesIds($rawData);
        $this->categoryLinkManagement->assignProductToCategories(
            $product->getSku(),
            $categoriesIds
        );
    }

    /**
     * Extract categories ids from $rawData
     *
     * @param array $rawData
     * @return array
     */
    public function getCategoriesIds($rawData)
    {
        $categoriesIds = [];
        $categoriesPathsStr = $this->getAttributeValue('categories', $rawData);
        $categoryPaths = explode(';', $categoriesPathsStr);
        foreach ($categoryPaths as $pathStr) {
            $path = explode('|', $pathStr);
            $category = $this->categoryHelper->getCategory($path);
            $categoriesIds[] = $category->getId();
        }

        return $categoriesIds;
    }
}