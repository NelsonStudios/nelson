<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-autocomplete
 * @version   1.2.10
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\SearchAutocomplete\Service;

use Mirasvit\SearchAutocomplete\Api\Service\CategoryProductInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CollectionFactory;

class CategoryProductService implements CategoryProductInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;


    /**
     * CategoryProductService constructor.
     * @param StoreManagerInterface $storeManager
     * @param LayerResolver $layerResolver
     * @param Visibility $productVisibility
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        LayerResolver $layerResolver,
        Visibility $productVisibility,
        CollectionFactory $collectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->layerResolver = $layerResolver;
        $this->productVisibility = $productVisibility;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCollection($index)
    {
        $productCollection = $this->layerResolver->get()->getProductCollection();

        $this->joinCategoryIndex($productCollection);
        $this->setAdditionalOptions($productCollection, $index);

        $categoryCollection = $this->getCategoryCollection($productCollection, $index);

        return $categoryCollection;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function joinCategoryIndex($collection)
    {
        $conditions = ['cat_index.product_id=e.entity_id',
            $collection->getSelect()->getConnection()
                ->quoteInto('cat_index.store_id=?', $this->storeManager->getStore()->getId())
        ];

        $visibility = $this->productVisibility->getVisibleInSearchIds();
        $conditions[] = $collection->getSelect()->getConnection()
            ->quoteInto('cat_index.visibility IN(?)', $visibility);
        $joinCond = join(' AND ', $conditions);

        $fromPart = $collection->getSelect()->getPart('from');
        if (isset($fromPart['cat_index'])) {
            $fromPart['cat_index']['joinCondition'] = $joinCond;
            $collection->getSelect()->columns(['cat_index_position' => 'cat_index.position', 'cat_index.category_id']);
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
        } else {
            $collection->getSelect()->join(
                ['cat_index' => $collection->getTable('catalog_category_product_index')],
                $joinCond,
                ['cat_index_position' => 'position', 'category_id']
            );
        }

        return $collection;
    }
 
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Mirasvit\Search\Api\Data\IndexInterface $index
     * @return mixed
     */
    private function setAdditionalOptions($collection, $index)
    {
        return $collection->getSelect()
            ->columns(['productsQTY' => 'count(e.entity_id)'])
            ->group('category_id')
            ->where('category_id!=?', $this->storeManager->getStore()->getRootCategoryId())
            ->order('productsQTY desc')
            ->limit($index->getLimit());
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Mirasvit\Search\Api\Data\IndexInterface $index
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|\Magento\Framework\Data\Collection\AbstractDb category collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCategoryCollection($collection, $index)
    {
        $categoryIds=[];
        $categoryIds = array_map(function ($data) {
            return $data['category_id'];
        }, $collection->getData());
        $categoryCollection =  $this->collectionFactory->create()
            ->addNameToResult()
            ->addFieldToFilter('is_active', ['eq' => 1])
            ->addFieldToFilter('level', ['gt' => 1])
            ->addFieldToFilter('path', ['like' => '%/'. $this->storeManager->getStore()->getRootCategoryId() .'/%'])
            ->addFieldToFilter('entity_id', ['in' => $categoryIds])
            ->setPageSize($index->getLimit());

        return $categoryCollection;
    }
}
