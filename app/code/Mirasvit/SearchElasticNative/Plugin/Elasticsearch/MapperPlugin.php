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



namespace Mirasvit\SearchElasticNative\Plugin\Elasticsearch;

use Mirasvit\SearchElasticNative\Adapter\Query\MatchQuery;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Framework\App\ScopeResolverInterface;

use Magento\Elasticsearch\Elasticsearch5\SearchAdapter\Mapper;
use Magento\Framework\Search\RequestInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext;

class MapperPlugin
{
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;
    /**
     * @var IndexNameResolver
     */
    private $indexNameResolver;
    /**
     * @var MatchQuery
     */
    private $matchQuery;

    /**
     * MapperPlugin constructor.
     * @param MatchQuery $matchQuery
     * @param IndexNameResolver $indexNameResolver
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        MatchQuery $matchQuery,
        IndexNameResolver $indexNameResolver,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->matchQuery = $matchQuery;
        $this->indexNameResolver = $indexNameResolver;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * @param Mapper $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return mixed
     */
    public function aroundBuildQuery(Mapper $subject, callable $proceed, RequestInterface $request)
    {
        $result = $proceed($request);

        if ($request->getSize() == 0 && $result['body']['size'] == 0) {
            $result['body']['size'] = 10000;
        }

        if ($request->getIndex() != Fulltext::INDEXER_ID) {
            $dimension = current($request->getDimensions());
            $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
            /** @var array $from */
            $from = $request->getFrom();
            $indexName = $from['index_name'];
            $indexName = $this->indexNameResolver->getIndexNameForAlias($storeId, $indexName);
            $result['index'] = $indexName;
            $result['body']['from'] = 0;
        }

        return $result;
    }
}
