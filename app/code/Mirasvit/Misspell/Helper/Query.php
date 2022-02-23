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
 * @package   mirasvit/module-misspell
 * @version   1.0.40
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Misspell\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Mirasvit\Misspell\Helper\Text as TextHelper;
use Mirasvit\Misspell\Model\SuggestFactory;
use Magento\Search\Model\SearchEngine;
use Magento\CatalogSearch\Model\Advanced\Request\BuilderFactory as RequestBuilderFactory;
use Magento\Framework\App\ScopeResolverInterface;

class Query extends AbstractHelper
{
    /**
     * @var array
     */
    protected $fallbackResult = [];

    /**
     * @var array
     */
    protected $fallbackCombination = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Search\Model\Query
     */
    protected $query;

    /**
     * @var \Mirasvit\Misspell\Helper\Text
     */
    protected $text;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * @var \Mirasvit\Misspell\Model\SuggestFactory
     */
    protected $suggestFactory;

    /**
     * @var QueryCollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var RequestBuilderFactory
     */
    private $requestBuilderFactory;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * Query constructor.
     * @param Context $context
     * @param QueryFactory $queryFactory
     * @param UrlFactory $urlFactory
     * @param SuggestFactory $suggestFactory
     * @param Text $textHelper
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param SearchEngine $searchEngine
     * @param RequestBuilderFactory $requestBuilderFactory
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        UrlFactory $urlFactory,
        SuggestFactory $suggestFactory,
        TextHelper $textHelper,
        QueryCollectionFactory $queryCollectionFactory,
        SearchEngine $searchEngine,
        RequestBuilderFactory $requestBuilderFactory,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->request                  = $context->getRequest();
        $this->query                    = $queryFactory->get();
        $this->text                     = $textHelper;
        $this->urlFactory               = $urlFactory;
        $this->suggestFactory           = $suggestFactory;
        $this->queryCollectionFactory   = $queryCollectionFactory;
        $this->searchEngine             = $searchEngine;
        $this->requestBuilderFactory    = $requestBuilderFactory;
        $this->scopeResolver            = $scopeResolver;

        parent::__construct($context);
    }

    /**
     * Query
     *
     * @return \Magento\Search\Model\Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    public function getQueryText()
    {
        return strip_tags($this->query->getQueryText());
    }

    /**
     * Old query text (before misspell)
     *
     * @return string
     */
    public function getMisspellText()
    {
        return strip_tags($this->request->getParam('o'));
    }

    /**
     * Old query text (before fallback)
     *
     * @return string
     */
    public function getFallbackText()
    {
        return strip_tags($this->request->getParam('f'));
    }

    /**
     * Misspell Url
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    public function getMisspellUrl($from, $to)
    {
        return $this->urlFactory->create()
            ->addQueryParams(['q' => $to, 'o' => $from])
            ->getUrl('*/*/*');
    }

    /**
     * Fallback Url
     *
     * @param string $from
     * @param string $to
     * @return string
     */
    public function getFallbackUrl($from, $to)
    {
        return $this->urlFactory->create()
            ->addQueryParams(['q' => $to, 'f' => $from])
            ->getUrl('*/*/*');
    }

    /**
     * Number results for specified search query
     *
     * @param string $query
     * @return int
     */
    public function getNumResults($query = null)
    {
        if ($query === null) {
            if ($this->query->getNumResults() === null) {
                return $this->getActualNumResults($this->query->getQueryText());
            }

            return (int) $this->query->getNumResults();
        }

        $collection = $this->queryCollectionFactory->create();

        foreach (explode(' ', trim($query)) as $term) {
            $collection->addFieldToFilter('query_text', ['like' => '%'. $term . '%']);
        }

        $collection->getSelect()->where('LENGTH(query_text) = '. strlen($query));
        $query = $collection->getFirstItem();

        if ($query->getId()) {
            return (int) $query->getNumResults();
        }

        return 1;
    }

    private function getActualNumResults(string $searchTerm)
    {
        $requestBuilder = $this->requestBuilderFactory->create()
            ->bind('search_term', $searchTerm)
            ->bindDimension('scope', $this->scopeResolver->getScope())
            ->setRequestName('catalogsearch_fulltext');

        $results = $this->searchEngine->search($requestBuilder->create());
        return $results->count();
    }

    /**
     * Suggest
     *
     * @param string $text
     * @return string
     */
    public function suggest($text)
    {
        $result = false;

        $model = $this->suggestFactory->create()->load($text);

        $suggest = $model->getSuggest();

        if ($this->text->strtolower($text) != $this->text->strtolower($suggest)) {
            $result = $suggest;
        }

        return $result;
    }

    /**
     * Fallback
     *
     * @param string $text
     * @return string
     */
    public function fallback($text)
    {
        $arQuery = $this->text->splitWords($text);
        $arQuery = array_slice($arQuery, 0, 5);

        foreach ($this->fallbackCombinations($arQuery) as $word) {
            $cntResults = $this->getNumResults($word);
            if ($cntResults > 1) {
                return trim($word);
            }
        }

        return false;
    }

    /**
     * Fallback combinations
     *
     * @param array $arQuery
     *
     * @return array
     */
    protected function fallbackCombinations(array $arQuery)
    {
        $combinations = [[]];
        foreach ($arQuery as $element) {
            foreach ($combinations as $combination) {
                array_push($combinations, array_merge(array($element), $combination));
            }
        }

        $combinations = array_map(function($subset){return implode(' ', array_reverse($subset));}, $combinations);
        $combinationsStrLen = array_map('strlen', $combinations);
        array_multisort($combinationsStrLen, $combinations);
        $combinations = array_reverse(array_filter($combinations));
        array_shift($combinations);

        return $combinations;
    }

    /**
     * Need add description
     *
     * @param int $start
     * @param int $choose
     * @param array $arr
     * @param int $n
     * @return void
     */
    protected function inner($start, $choose, $arr, $n)
    {
        if ($choose == 0) {
            array_push($this->fallbackResult, $this->fallbackCombination);
        } else {
            for ($i = $start; $i <= $n - $choose; ++$i) {
                array_push($this->fallbackCombination, $arr[$i]);
                $this->inner($i + 1, $choose - 1, $arr, $n);
                array_pop($this->fallbackCombination);
            }
        }
    }
}
