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



namespace Mirasvit\Search\Model;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Search\Api\Data\IndexInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Api\Service\IndexServiceInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Mirasvit\Search\Service\StemmingService;

use Mirasvit\Search\Model\Config;

class AutocompleteProvider
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var IndexServiceInterface
     */
    private $indexService;

    /**
     * @var QueryCollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    private $resourceHelper;

    /**
     * @var StemmingService
     */
    private $stemmingService;

    /**
     * @var Config
     */
    private $config;

    /**
     * AutocompleteProvider constructor.
     * @param IndexRepositoryInterface $indexRepository
     * @param IndexServiceInterface $indexService
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param QueryFactory $queryFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param StemmingService $stemmingService
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        IndexServiceInterface $indexService,
        QueryCollectionFactory $queryCollectionFactory,
        QueryFactory $queryFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Helper $resourceHelper,
        StemmingService $stemmingService,
        Config $config
    ) {
        $this->indexRepository = $indexRepository;
        $this->indexService = $indexService;
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->queryFactory = $queryFactory;
        $this->storeManager = $storeManager;
        $this->resourceHelper = $resourceHelper;
        $this->stemmingService = $stemmingService;
        $this->config = $config;
    }

    /**
     * @param int $id
     * @return IndexInterface
     */
    public function get($id)
    {
        return $this->indexRepository->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getIndices()
    {
        $indices = [];
        $collection = $this->indexRepository->getCollection()
            ->addFieldToFilter(IndexInterface::IS_ACTIVE, 1);

        /** @var IndexInterface $index */
        foreach ($collection as $index) {
            $indexDataObject = new DataObject([
                'identifier' => $index->getIdentifier(),
                'title'      => $index->getTitle(),
                'properties' => $index->getProperties(),
                'index_id'   => $index->getIndexId(),
            ]);

            if (in_array($index->getIdentifier(), Config::DISALLOWED_MULTIPLE)) {
                $indexDataObject['index_id'] = $index->getIndexId();
            }

            $indices[] = $indexDataObject;
        }

        $indices[] = new DataObject([
            'title'      => __('Popular suggestions')->__toString(),
            'identifier' => 'magento_search_query',
        ]);

        $indices[] = new DataObject([
            'title'      => __('Products in categories')->__toString(),
            'identifier' => 'magento_catalog_categoryproduct',
        ]);

        return $indices;
    }

    /**
     * @param IndexInterface $index
     * @return \Magento\Framework\Data\Collection|\Magento\Search\Model\ResourceModel\Query\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection($index)
    {
        switch ($index->getIdentifier()) {
            case 'magento_search_query':
                $ignoreWords = ['join','select','from','char','order','inner','where'];
                $query = $this->queryFactory->get();
                $queryText = $this->stemmingService->singularize($query->getQueryText());
                $exceptions = $this->config->getWildcardExceptions();
                if (in_array($query->getQueryText(), $exceptions) || in_array($queryText, $exceptions)) {
                    $queryText = ' '. $queryText .' ';
                }

                $collection = $this->queryCollectionFactory->create();

                $collection->getSelect()->reset(
                    \Magento\Framework\DB\Select::FROM
                )->distinct(
                    true
                )->from(
                    ['main_table' => $collection->getTable('search_query')]
                )->where(
                    'num_results > 0 AND display_in_terms = 1 AND query_text LIKE ?',
                    $this->resourceHelper->addLikeEscape($queryText, ['position' => 'any'])
                )->order(
                    'popularity ' . \Magento\Framework\DB\Select::SQL_DESC
                );

                foreach ($ignoreWords as $ignoreWord) {
                    $collection->getSelect()->where('query_text not like "%'. $ignoreWord .'%"');
                }

                $collection->addFieldToFilter('query_text', ['nin' => [$query->getQueryText(), $queryText]])
                    ->addStoreFilter([$this->storeManager->getStore()->getId()])
                    ->setOrder('popularity')
                    ->distinct(true);
                return $collection;

                break;

            case 'magento_catalog_categoryproduct':
                $index = $this->indexRepository->get('catalogsearch_fulltext');
                break;

            default:
                if ($index->getIndexId()) {
                    $index = $this->indexRepository->get($index->getIndexId());
                } else {
                    $index = $this->indexRepository->get($index->getIdentifier());
                }
                break;
        }

        return $this->indexService->getSearchCollection($index);
    }

    /**
     * @param string $index
     * @return bool|\Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse($index)
    {
        $identifier = $index->getIdentifier();
        $customInstances = [
            'magento_search_query',
            'magento_catalog_categoryproduct',
        ];
        if (in_array($identifier, $customInstances)) {
            return false;
        }
        $index = $this->indexRepository->get($identifier);

        return $this->indexService->getQueryResponse($index);
    }
}
