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


namespace Mirasvit\Search\Index\Magento\Cms\Page;

use Mirasvit\Search\Model\Index\AbstractIndex;
use Mirasvit\Search\Model\Index\Context;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Mirasvit\Search\Service\ContentService;

/**
 * @method array getIgnoredPages()
 */
class Index extends AbstractIndex
{
    /**
     * @var PageCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ContentService
     */
    protected $contentService;

    /**
     * Index constructor.
     * @param PageCollectionFactory $collectionFactory
     * @param ContentService $contentService
     * @param Context $context
     * @param array $dataMappers
     */
    public function __construct(
        PageCollectionFactory $collectionFactory,
        ContentService $contentService,
        Context $context,
        $dataMappers
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->contentService    = $contentService;

        parent::__construct($context, $dataMappers);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Magento / Cms Page';
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'magento_cms_page';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [
            'title' => __('Title'),
            'content' => __('Content'),
            'content_heading' => __('Content Heading'),
            'meta_keywords' => __('Meta Keywords'),
            'meta_description' => __('Meta Description'),
            'landing_page'     => __('CMS Block'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey()
    {
        return 'page_id';
    }

    /**
     * {@inheritdoc}
     */
    public function buildSearchCollection()
    {
        $collection = $this->collectionFactory->create();

        $this->context->getSearcher()->joinMatches($collection, 'main_table.page_id');

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = null, $limit = 100)
    {
        $collection = $this->collectionFactory->create()
            ->addStoreFilter($storeId)
            ->addFieldToFilter('is_active', 1);

        $ignored = $this->getModel()->getProperty('ignored_pages');
        if (is_array($ignored) && count($ignored)) {
            $collection->addFieldToFilter('identifier', ['nin' => $ignored]);
        }

        if ($entityIds) {
            $collection->addFieldToFilter('page_id', $entityIds);
        }

        $collection
            ->addFieldToFilter('page_id', ['gt' => $lastEntityId])
            ->setPageSize($limit)
            ->setOrder('page_id', 'asc');

        foreach ($collection as $item) {
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
