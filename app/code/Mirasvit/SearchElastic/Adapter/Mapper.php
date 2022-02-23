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
 * @package   mirasvit/module-search-elastic
 * @version   1.2.75
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchElastic\Adapter;

use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\SearchElastic\Model\Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mapper
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var IndexBuilderInterface[]
     */
    private $indexProviders;

    /**
     * @var Query\MatchQuery
     */
    private $matchQuery;

    /**
     * @var Query\FilterQuery
     */
    private $filterQuery;

    /**
     * @var Query\AggregationQuery
     */
    private $aggregationQuery;

    /**
     * @var IndexScopeResolver
     */
    private $resolver;
    /**
     * @var Config
     */
    private $config;

    /**
     * Mapper constructor.
     * @param IndexRepositoryInterface $indexRepository
     * @param ConditionManager $conditionManager
     * @param Query\MatchQuery $matchQuery
     * @param Query\FilterQuery $filterQuery
     * @param Query\AggregationQuery $aggregationQuery
     * @param Config $config
     * @param IndexScopeResolver $resolver
     * @param array $indexProviders
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        ConditionManager $conditionManager,
        Query\MatchQuery $matchQuery,
        Query\FilterQuery $filterQuery,
        Query\AggregationQuery $aggregationQuery,
        Config $config,
        IndexScopeResolver $resolver,
        array $indexProviders
    ) {
        $this->indexRepository  = $indexRepository;
        $this->conditionManager = $conditionManager;
        $this->indexProviders   = $indexProviders;
        $this->matchQuery       = $matchQuery;
        $this->resolver         = $resolver;
        $this->config           = $config;
        $this->filterQuery      = $filterQuery;
        $this->aggregationQuery = $aggregationQuery;
    }

    /**
     * @param RequestInterface $request
     *
     * @return array
     */
    public function buildQuery(RequestInterface $request)
    {
        if (is_array($request->getFrom())) {
            $indexName = $this->resolver->resolve(
                $request->getFrom()['index_name'],
                $request->getDimensions()
            );
        } else {
            $searchIndex = $this->indexRepository->get($request->getIndex());
            $indexName   = $this->resolver->resolve(
                $searchIndex->getIdentifier(),
                $request->getDimensions()
            );
        }

        $query = [
            'index' => $this->config->getIndexName($indexName),
            'body'  => [
                'from'          => is_scalar($request->getFrom()) ? $request->getFrom() : 0,
                'size'          => 1000000 - 1,//$request->getSize(), magento apply sorting AFTER selecting products, so we should return products without limits
                'stored_fields' => ['_id', '_score'],
                'query'         => $this->processQuery($request->getQuery(), [], BoolQuery::QUERY_CONDITION_MUST),
                //'_source'       => [],
            ],
        ];

        if (isset($query['body']['query']['bool']['should']) && !empty($query['body']['query']['bool']['should'])) {
            $query['body']['query']['bool']['minimum_should_match'] = 1;
        }

        $aggregations = $this->aggregationQuery->build($request);

        if ($aggregations) {
            $query['body']['aggregations'] = $aggregations;
        }

        return $query;
    }

    /**
     * @param RequestQueryInterface $requestQuery
     * @param array                 $query
     * @param string                $conditionType
     *
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function processQuery(RequestQueryInterface $requestQuery, array $query, $conditionType)
    {
        switch ($requestQuery->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var BoolQuery $requestQuery */
                $query = $this->processBoolQuery($requestQuery, $query);
                break;

            case RequestQueryInterface::TYPE_MATCH:
                /** @var MatchQuery $requestQuery */
                $query = $this->matchQuery->build($query, $requestQuery);
                break;

            case RequestQueryInterface::TYPE_FILTER:
                /** @var FilterQuery $requestQuery */
                $query = $this->processFilterQuery($requestQuery, $query, $conditionType);
                break;

            default:
                throw new \Exception("Not implemented");
        }

        return $query;
    }

    /**
     * @param BoolQuery $query
     * @param array     $selectQuery
     *
     * @return array
     */
    protected function processBoolQuery(BoolQuery $query, array $selectQuery)
    {
        $selectQuery = $this->processBoolQueryCondition(
            $query->getMust(),
            $selectQuery,
            BoolQuery::QUERY_CONDITION_MUST
        );

        $selectQuery = $this->processBoolQueryCondition(
            $query->getShould(),
            $selectQuery,
            BoolQuery::QUERY_CONDITION_SHOULD
        );

        $selectQuery = $this->processBoolQueryCondition(
            $query->getMustNot(),
            $selectQuery,
            BoolQuery::QUERY_CONDITION_NOT
        );

        return $selectQuery;
    }

    /**
     * @param array  $subQueryList
     * @param array  $query
     * @param string $conditionType
     *
     * @return array
     */
    protected function processBoolQueryCondition(array $subQueryList, array $query, $conditionType)
    {
        foreach ($subQueryList as $subQuery) {
            $query = $this->processQuery($subQuery, $query, $conditionType);
        }

        return $query;
    }

    /**
     * @param FilterQuery $query
     * @param array       $selectQuery
     * @param string      $conditionType
     *
     * @return array
     * @throws \Exception
     */
    protected function processFilterQuery(FilterQuery $query, array $selectQuery, $conditionType)
    {
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_FILTER:
                $filterQuery = $this->filterQuery->build($query->getReference(), $conditionType);
                foreach ($filterQuery['bool'] as $condition => $filter) {
                    $selectQuery['bool'][$condition] = array_merge(
                        isset($selectQuery['bool'][$condition]) ? $selectQuery['bool'][$condition] : [],
                        $filter
                    );
                }
                break;
            default:
                throw new \Exception("Filter query not implemented");
        }

        return $selectQuery;
    }
}
