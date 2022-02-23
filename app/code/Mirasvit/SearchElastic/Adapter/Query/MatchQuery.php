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

use Magento\Framework\Search\Request\Query\Match;
use Magento\Framework\Search\Request\QueryInterface;
use Mirasvit\Search\Api\Service\QueryServiceInterface;
use Mirasvit\Search\Model\Config;

class MatchQuery
{
    private $queryService;

    private $searchTerms = [];

    public function __construct(
        QueryServiceInterface $queryService
    ) {
        $this->queryService = $queryService;
    }

    /**
     * @param array          $query
     * @param QueryInterface $matchQuery
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function build(array $query, QueryInterface $matchQuery)
    {
        /** @var Match $matchQuery */

        $searchQuery = $this->queryService->build($matchQuery->getValue());

        $fields = ['options' => 1];
        foreach ($matchQuery->getMatches() as $match) {
            $field = $match['field'];
            if ($field == '*') {
                continue;
            }

            $boost          = isset($match['boost']) ? intval((string)$match['boost']) : 1; //sometimes boots is a Phrase
            $fields[$field] = $boost;
        }

        $query['bool']['must'][]['query_string'] = [
            'fields' => array_keys($fields),
            'query'  => $this->compileQuery($searchQuery),
        ];

        $booster = 1;
        $useBooster = false;
        $this->searchTerms = array_unique(array_filter($this->searchTerms));

        foreach ($this->searchTerms as $key => $term) {
            if (strlen($term) == 1) {
                $useBooster = true;
            }
        }

        foreach ($fields as $field => $boost) {
            foreach ($this->searchTerms as $term) {
                if ($useBooster) {
                    $booster = strlen($term) / strlen($matchQuery->getValue());
                }

                if (strlen($term) == 1) {
                    $query['bool']['should'][]['wildcard'][$field] = [
                        'value' => $term,
                        'boost' => pow(2, round($boost * $booster/2)),
                    ];
                } else {
                    $query['bool']['should'][]['wildcard'][$field] = [
                        'value' => $term,
                        'boost' => pow(2, round($boost * $booster)),
                    ];
                }
            }
        }

        return $query;
    }

    /**
     * @param array $query
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function compileQuery($query)
    {
        $compiled = [];
        foreach ($query as $directive => $value) {
            switch ($directive) {
                case '$like':
                    $compiled[] = '(' . $this->compileQuery($value) . ')';
                    break;

                case '$!like':
                    $compiled[] = '(NOT ' . $this->compileQuery($value) . ')';
                    break;

                case '$and':
                    $and = [];
                    foreach ($value as $item) {
                        $and[] = $this->compileQuery($item);
                    }
                    $compiled[] = '(' . implode(' AND ', $and) . ')';
                    break;

                case '$or':
                    $or = [];
                    foreach ($value as $item) {
                        $or[] = $this->compileQuery($item);
                    }
                    $compiled[] = '(' . implode(' OR ', $or) . ')';
                    break;

                case '$term':
                    $phrase = $this->escape($value['$phrase']);
                    switch ($value['$wildcard']) {
                        case Config::WILDCARD_INFIX:
                            $compiled[] = "$phrase OR *$phrase*";
                            $this->searchTerms[] = $phrase;
                            $this->searchTerms[] = "*$phrase*";
                            break;
                        case Config::WILDCARD_PREFIX:
                            $compiled[] = "$phrase OR *$phrase";
                            $this->searchTerms[] = $phrase;
                            $this->searchTerms[] = "*$phrase";
                            break;
                        case Config::WILDCARD_SUFFIX:
                            $compiled[] = "$phrase OR $phrase*";
                            $this->searchTerms[] = $phrase;
                            $this->searchTerms[] = "$phrase*";
                            break;
                        case Config::WILDCARD_DISABLED:
                            $compiled[] = $phrase;
                            $this->searchTerms[] = $phrase;
                            break;
                    }
                    break;
            }
        }

        return implode(' AND ', $compiled);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function escape($value)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }
}
