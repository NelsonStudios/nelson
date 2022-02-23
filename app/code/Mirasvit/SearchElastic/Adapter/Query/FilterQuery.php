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



namespace Mirasvit\SearchElastic\Adapter\Query;

use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;

class FilterQuery
{
    /**
     * @var Filter\WildcardFilter
     */
    private $wildcardFilter;
    /**
     * @var Filter\RangeFilter
     */
    private $rangeFilter;
    /**
     * @var Filter\TermFilter
     */
    private $termFilter;

    /**
     * FilterQuery constructor.
     * @param Filter\TermFilter $termFilter
     * @param Filter\RangeFilter $rangeFilter
     * @param Filter\WildcardFilter $wildcardFilter
     */
    public function __construct(
        Filter\TermFilter $termFilter,
        Filter\RangeFilter $rangeFilter,
        Filter\WildcardFilter $wildcardFilter
    ) {
        $this->termFilter = $termFilter;
        $this->rangeFilter = $rangeFilter;
        $this->wildcardFilter = $wildcardFilter;
    }

    /**
     * @param FilterInterface $filter
     * @param string          $conditionType
     * @return array
     * @throws \Exception
     */
    public function build(FilterInterface $filter, $conditionType)
    {
        if ($conditionType == BoolQuery::QUERY_CONDITION_NOT) {
            $conditionType = 'must_not';
        }

        if ($filter->getType() == FilterInterface::TYPE_TERM) {
            /** @var \Magento\Framework\Search\Request\Filter\Term $filter */
            $query = [
                'bool' => [
                    $conditionType => $this->termFilter->build($filter),
                ],
            ];
        } elseif ($filter->getType() == FilterInterface::TYPE_RANGE) {
            /** @var \Magento\Framework\Search\Request\Filter\Range $filter */
            $query = [
                'bool' => [
                    $conditionType => $this->rangeFilter->build($filter),
                ],
            ];
        } elseif ($filter->getType() == FilterInterface::TYPE_WILDCARD) {
            /** @var \Magento\Framework\Search\Request\Filter\Wildcard $filter */
            $query = [
                'bool' => [
                    $conditionType => $this->wildcardFilter->build($filter),
                ],
            ];
        }

        return $query;
    }
}
