<?php

namespace Fecon\OrsProducts\Helper;

/**
 * Category Helper
 */
class CategoryHelper
{

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collecionFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collecionFactory
    ) {
        $this->collectionFactory = $collecionFactory;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function createCategory($path)
    {
        $categoryName = array_pop($path);
        $parentCategory = $this->getCategory($path);
        $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
        $data = [
            'name' => $categoryName,
            "is_active" => true,
            "include_in_menu" => false,
        ];
        $category->addData($data);
        $category->setPath($parentCategory->getPath());
        $category->setParentId($parentCategory->getId());
        $category->setAttributeSetId($category->getDefaultAttributeSetId());
        $category->save();

        return $category;
    }

    public function getCategory($path)
    {
        $category = false;
        $categoryName = array_pop($path);
        $collection = $this->collectionFactory
            ->create()
            ->addAttributeToFilter('name', $categoryName)
            ->setPageSize(1);

        if ($collection->getSize()) {
            $categoryId = $collection->getFirstItem()->getId();
            $category = $this->objectManager->create(\Magento\Catalog\Model\Category::class);
            $category->load($categoryId);
        } else {
            array_push($path, $categoryName);
            $category = $this->createCategory($path);
        }

        return $category;
    }
}