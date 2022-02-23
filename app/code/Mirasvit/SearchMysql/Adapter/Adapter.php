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
 * @package   mirasvit/module-search-mysql
 * @version   1.0.40
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchMysql\Adapter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder as AggregationBuilder;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Mirasvit\Search\Model\Config as SearchConfig;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Adapter implements AdapterInterface
{
    /**
     * Mapper instance
     * @var Mapper
     */
    protected $mapper;

    /**
     * Response Factory
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var AggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * @var TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /**
     * @var SearchConfig
     */
    protected $searchConfig;

    /**
     * Adapter constructor.
     *
     * @param Mapper                  $mapper
     * @param ResponseFactory         $responseFactory
     * @param ResourceConnection      $resource
     * @param AggregationBuilder      $aggregationBuilder
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param SearchConfig            $searchConfig
     */
    public function __construct(
        Mapper $mapper,
        ResponseFactory $responseFactory,
        ResourceConnection $resource,
        AggregationBuilder $aggregationBuilder,
        TemporaryStorageFactory $temporaryStorageFactory,
        SearchConfig $searchConfig
    ) {
        $this->mapper                  = $mapper;
        $this->responseFactory         = $responseFactory;
        $this->resource                = $resource;
        $this->aggregationBuilder      = $aggregationBuilder;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->searchConfig            = $searchConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function query(RequestInterface $request)
    {
        $this->resource->getConnection()->query('SET SESSION group_concat_max_len = 1000000;');

        $query = $this->mapper->buildQuery($request);

        if ($request->getName() == 'quick_search_container') {
            $query->limit($this->searchConfig->getResultsLimit());

            //ability to search by 0 attribute (options)
            $from = $this->processFromPart($query->getPart('FROM'));
            $query->setPart('FROM', $from);
        }

        // @codingStandardsIgnoreStart
        if (filter_input(INPUT_GET, 'debug') !== null) {
            var_dump($query->__toString());
        }
        // @codingStandardsIgnoreEnd

        $temporaryStorage = $this->temporaryStorageFactory->create();
        $table            = $temporaryStorage->storeDocumentsFromSelect($query);
        $documents        = $this->getDocuments($table);
        $aggregations     = $this->aggregationBuilder->build($request, $table, $documents);

        $response = [
            'documents'    => $documents,
            'aggregations' => $aggregations,
            'total' => count($documents),
        ];

        return $this->responseFactory->create($response);
    }

    /**
     * Executes query and return raw response
     *
     * @param Table $table
     *
     * @return array
     * @throws \Zend_Db_Exception
     */
    private function getDocuments(Table $table)
    {
        $connection = $this->getConnection();
        $select     = $connection->select();
        $select->from($table->getName(), ['entity_id', 'score']);

        return $connection->fetchAssoc($select);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * @param array $from
     *
     * @return array
     */
    private function processFromPart($from)
    {
        foreach (array_keys($from) as $k) {
            $fromConditions = explode('INNER JOIN', $from[$k]['tableName']);
            foreach ($fromConditions as $key => $condition) {
                if (strpos($condition, 'search_index.attribute_id = cea.attribute_id') !== false) {
                    $from[$k]['tableName'] = str_replace('INNER JOIN' . $condition, 'LEFT JOIN'
                        . $condition, $from[$k]['tableName']);
                    $from[$k]['tableName'] = new \Zend_Db_Expr('(' . $from[$k]['tableName'] . ')');
                }
            }
        }

        return $from;
    }
}
