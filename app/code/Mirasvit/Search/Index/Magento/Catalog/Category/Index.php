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
 * @package   mirasvit/module-search
 * @version   1.0.156
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Index\Magento\Catalog\Category;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Mirasvit\Search\Service\ContentService;

class Index extends AbstractIndex
{
    /**
     * @var CategoryCollectionFactory
     */
    private $collectionFactory;

    private $contentService;

    /**
     * @param CategoryCollectionFactory $collectionFactory
     * @param ContentService            $contentService
     * @param Context                   $context
     * @param array                     $dataMappers
     */
    public function __construct(
        CategoryCollectionFactory $collectionFactory,
        ContentService $contentService,
        Context $context,
        $dataMappers
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->contentService    = $contentService;

        parent::__construct($context, $dataMappers);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Magento / Category';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'magento_catalog_category';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'name'             => __('Name'),
            'description'      => __('Description'),
            'meta_title'       => __('Page Title'),
            'meta_keywords'    => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
            'landing_page'     => __('CMS Block'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'entity_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        $collection = $this->collectionFactory->create()
            ->addNameToResult()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('level', ['gt' => 1]);

        if (strpos($collection->getSelect(), '`e`') !== false) {
            $this->context->getSearcher()->joinMatches($collection, 'e.entity_id');
        } else {
            $this->context->getSearcher()->joinMatches($collection, 'main_table.entity_id');
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->context->getStoreManager()->getStore($storeId);

        $root = $store->getRootCategoryId();

        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect(array_keys($this->getAttributes()))
            ->setStoreId($storeId)
            ->addPathsFilter("1/$root/")
            ->addFieldToFilter('is_active', 1);

        if ($entityIds) {
            $collection->addFieldToFilter('entity_id', ['in' => $entityIds]);
        }

        $collection->addFieldToFilter('entity_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('entity_id');

        $collection->getSelect()
            ->joinInner(
                ['category_product' => $collection->getResource()->getTable('catalog_category_product')],
                'e.entity_id = category_product.category_id',
                []
            )
            ->group('e.entity_id');

        foreach ($collection as $item) {
            $item->setData(
                'description',
                $this->contentService->processHtmlContent($storeId, $item->getData('description'))
            );

            $item->setData('landing_page', $this->renderCmsBlock($item->getData('landing_page'), $storeId));
        }

        return $collection;
    }


    /**
     * @param int $blockId
     * @param int $storeId
     *
     * @return string
     */
    protected function renderCmsBlock($blockId, $storeId)
    {
        if ($blockId == 0) {
            return '';
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        try {
            /** @var \Magento\Cms\Api\BlockRepositoryInterface $blockRepository */
            $blockRepository = $objectManager->get('Magento\Cms\Api\BlockRepositoryInterface');

            $block = $blockRepository->getById($blockId);

            return $this->contentService->processHtmlContent($storeId, $block->getContent());
        } catch (\Exception $e) {
        }

        return '';
    }
}
