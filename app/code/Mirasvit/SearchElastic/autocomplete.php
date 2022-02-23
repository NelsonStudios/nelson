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


// @codingStandardsIgnoreStart
namespace Mirasvit\SearchElastic;

use Mirasvit\SearchElastic\Model\Engine;
use Elasticsearch\ClientBuilder;
use Mirasvit\SearchElastic\Helper\Stemming\En;
use Mirasvit\SearchElastic\Helper\Stemming\Nl;
use Mirasvit\SearchElastic\Helper\Stemming\Ru;

if (php_sapi_name() == "cli") {
    return;
}

$configFile = dirname(dirname(dirname(__DIR__))) . '/etc/autocomplete.json';

if (stripos(__DIR__, 'vendor') !== false) {
    $configFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/app/etc/autocomplete.json';
}

if (!file_exists($configFile)) {
    return;
}

$config = \Zend_Json::decode(file_get_contents($configFile));

if ($config['engine'] !== 'elastic') {
    return;
}

class ElasticAutocomplete
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var array
     */
    private $locales = [];
    /**
     * @var array
     */
    private $shouldConditions = [];
    /**
     * @var array
     */
    private $notTerms = [];

    /**
     * ElasticAutocomplete constructor.
     * @param array $config
     * @param En $En
     * @param Nl $Nl
     * @param Ru $Ru
     */
    public function __construct(
        array $config,
        En $En,
        Nl $Nl,
        Ru $Ru
    ) {
        $this->config = $config;
        $this->locales = ['en' => $En, 'nl' => $Nl, 'ru' => $Ru];
        $this->shouldConditions = [];
        $this->notTerms = [];
    }

    /**
     * @return array
     */
    public function process()
    {
        $result = [];
        $totalItems = 0;
        $result['indices'] = [];

        foreach ($this->config['indexes'][$this->getStoreId()] as $identifier => $config) {
            if($config['identifier'] == 'catalogsearch_fulltext'){
                $condition = [
                    [
                        'terms' => [
                            'visibility_raw' => ['3', '4'],
                        ],
                    ],
                    [
                        'query_string' => [
                            'fields' => $this->getWeights($identifier),
                            'query'  => '('. $this->getQuery($identifier) .')',
                        ],
                    ],
                    [
                        'term' => ['is_in_stock_raw' => 1, ],
                    ],
                ];
            } else {
                $condition = [
                    [
                        'query_string' => [
                            'fields' => $this->getWeights($identifier),
                            'query'  => '('. $this->getQuery($identifier) .')',
                        ],
                    ],
                ];
            }
            $query = [
                'index' => $config['index'],
                'body'  => [
                    'from' => 0,
                    'size'  => $config['limit'],
                    'query' => [
                        'bool' => [
                            'must' => $condition,
                            'should' => $this->shouldConditions,
                            'must_not' => [
                                'query_string' => [
                                    'fields' => $this->getWeights($identifier),
                                    'query'  => $this->getNotTermsQuery(),
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            if (version_compare($this->config['esVersion'], '7.0.0', '<')) {
                $query['type'] = 'doc';
            }

            try {
                $response = $this->getClient()->search($query);
                $total = 0;
                if (version_compare($this->config['esVersion'], '7.0.0', '<')) {
                    $total = $response['hits']['total'];
                } else {
                    $total = $response['hits']['total']['value'];
                }

                $items = $this->mapHits($response['hits']['hits'], $config);

                if ($total && $items) {
                    $result['indices'][] = [
                        'identifier'   => $identifier == 'catalogsearch_fulltext' ? 'magento_catalog_product' : $identifier,
                        'isShowTotals' => true,
                        'order'        => $config['order'],
                        'title'        => $config['title'],
                        'totalItems'   => $total,
                        'items'        => $items,
                    ];
                    $totalItems += $total;
                }
            } catch (\Exception $e) {
            }
        }

        $result['query'] = htmlspecialchars($this->getQueryText());
        $result['totalItems'] = $totalItems;
        $result['noResults'] = $totalItems == 0;
        $result['textEmpty'] = sprintf($this->config['textEmpty'][$this->getStoreId()], htmlspecialchars($this->getQueryText()));
        $result['textAll'] = sprintf($this->config['textAll'][$this->getStoreId()], $result['totalItems']);
        $result['urlAll'] = $this->config['urlAll'][$this->getStoreId()] . htmlspecialchars($this->getQueryText());

        return $result;
    }

    /**
     * @return \Elasticsearch\Client
     */
    private function getClient()
    {
        $client = ClientBuilder::fromConfig([
            'hosts' => [$this->config['host'] . ':' . $this->config['port']],
        ]);

        return $client;
    }

    /**
     * @param string $identifier
     * @return array
     */
    private function getWeights($identifier)
    {
        $weights = [
            'options',
        ];
        foreach ($this->config['indexes'][$this->getStoreId()][$identifier]['fields'] as $f => $w) {
            $weights[] = $f;
        }

        return $weights;
    }

    /**
     * @return mixed|string
     */
    private function getQueryText()
    {
        return filter_input(INPUT_GET, 'q') != null
            ? filter_input(INPUT_GET, 'q')
            : '';
    }

    /**
     * @return mixed
     */
    private function getStoreId()
    {
        return filter_input(INPUT_GET, 'store_id') != null
        && is_numeric(filter_input(INPUT_GET, 'store_id'))
            ? filter_input(INPUT_GET, 'store_id')
            : array_keys($this->config['indexes'])[0];
    }

    /**
     * @return mixed
     */
    private function getLocale()
    {
        return $this->config['advancedConfig']['locale'][$this->getStoreId()];
    }

    /**
     * @param string $terms
     * @param string $identifier
     */
    private function collectShouldConditions($terms, $identifier)
    {
        if (is_array($terms)){
            foreach ($terms as $term) {
                foreach ($this->config['indexes'][$this->getStoreId()][$identifier]['fields'] as $f => $w) {
                    $term = str_replace(['(',')'], '', $term);
                    $this->shouldConditions[] = ['wildcard' => [$f => ['value' => explode(' OR ', $term)[0], 'boost' => pow(2, $w)+1,],],];
                    $this->shouldConditions[] = ['wildcard' => [$f => ['value' => explode(' OR ', $term)[1], 'boost' => pow(2, $w),],],];
                }
            }
        } else {
            foreach ($this->config['indexes'][$this->getStoreId()][$identifier]['fields'] as $f => $w) {
                $term = str_replace(['(',')'], '', $this->getWildcard($terms));
                $this->shouldConditions[] = ['wildcard' => [$f => ['value' => explode(' OR ', $term)[0], 'boost' => pow(2, $w)+1,],],];
                $this->shouldConditions[] = ['wildcard' => [$f => ['value' => explode(' OR ', $term)[1], 'boost' => pow(2, $w),],],];
            }
        }
    }

    /**
     * @return string
     */
    private function getNotTermsQuery()
    {
        $notTermsQuery = '';
        if (!empty($this->notTerms)) {
            $notTermsQuery = '('. implode(' OR ', $this->notTerms) .')';
        }

        return $notTermsQuery;
    }

    /**
     * @param string $identifier
     * @return string
     */
    private function getQuery($identifier)
    {
        $terms = array_filter(explode(" ", $this->getQueryText()));

        $conditions = [];
        foreach ($terms as $term) {
            $term = mb_strtolower($term);
            $queryPart = $this->prepareQuery($term);

            if (!empty($queryPart)){
                $conditions[] = $queryPart;
            } else {
                $this->collectShouldConditions($this->escape($term), $identifier);
            }

        }

        $notKey = array_search('!', $conditions, true);

        if ($notKey > 0) {
            $this->notTerms = array_slice($conditions, $notKey+1);
            $conditions = array_slice($conditions, 0, $notKey);
        }

        $this->collectShouldConditions($conditions, $identifier);

        return implode(" AND ", $conditions);
    }

    /**
     * @param string $term
     * @return string
     */
    private function prepareQuery($term)
    {
        $searchTerm = [];

        if (in_array($term, $this->config['advancedConfig']['not_words'])) {
            return '!';
        }

        if (isset($this->config['advancedConfig']['stopwords'][$this->getStoreId()])) {
            if (in_array($term, explode(',', $this->config['advancedConfig']['stopwords'][$this->getStoreId()]))) {
                return '';
            }
        }

        if (isset($this->config['advancedConfig']['replace_words'][$term])){
            $term = $this->config['advancedConfig']['replace_words'][$term];
        }

        $searchTerm[] = $this->getLongTail($term);
        $term = $this->escape($term);
        $searchTerm[] = $this->getWildcard($term);
        $searchTerm[] = $this->lemmatize($term);
        $searchTerm[] = $this->getSynonyms($term);
        $searchTerm = array_filter($searchTerm);
        $searchTerm = array_unique($searchTerm);

        return '('. implode(' OR ', $searchTerm) .')';
    }

    /**
     * @param string $term
     * @return string
     */
    private function getWildcard($term)
    {
        if (in_array($term, $this->config['advancedConfig']['wildcard_exceptions'])) {
            return $term;
        }

        $result = [];
        $result[] = $term;

        switch ($this->config['advancedConfig']['wildcard']) {
            case 'infix':
                if (strlen($term) > 1) {
                    $result[] = '*'. $term .'*';
                } else {
                    $result[] = $term .'*';
                }
                break;
            case 'suffix':
                $result[] = $term .'*';
                break;
            case 'prefix':
                $result[] = '*'. $term;
                break;
            default:
                break;
        }

        return implode(' OR ', $result);
    }

    /**
     * @param string $term
     * @return string
     */
    private function lemmatize($term)
    {
        if (array_key_exists($this->getLocale(), $this->locales)) {
            return $this->getWildcard($this->locales[$this->getLocale()]->singularize($term));
        } else {
            return '';
        }
    }

    /**
     * @param string $term
     * @return string
     */
    private function getLongTail($term)
    {
        $result = [];
        if (!empty($this->config['advancedConfig']['long_tail'])) {
            foreach ($this->config['advancedConfig']['long_tail'] as $expr) {
                $matches = null;
                preg_match_all($expr['match_expr'], $term, $matches);

                foreach ($matches[0] as $math) {
                    $math = preg_replace($expr['replace_expr'], $expr['replace_char'], $math);
                    if ($math) {
                        $result[] = $math;
                    }
                }
            }
        }

        return implode(' OR ', $result);
    }

    /**
     * @param string $term
     * @return string
     */
    private function getSynonyms($term)
    {
        $result = [];
        if (isset($this->config['advancedConfig']['synonyms'][$this->getStoreId()])) {
            if (array_key_exists($term, $this->config['advancedConfig']['synonyms'][$this->getStoreId()])) {
                foreach (explode(',', $this->config['advancedConfig']['synonyms'][$this->getStoreId()][$term]) as $synonym) {
                    $result[] = $synonym;
                }
            }
        }

        return implode(' OR ', $result);
    }

    /**
     * @param string $value
     * @return string|string[]|null
     */
    private function escape($value)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }

    /**
     * @param array $hits
     * @param mixed $config
     * @return array
     */
    private function mapHits($hits, $config)
    {
        $items = [];
        foreach ($hits as $hit) {
            if (count($items) > $config['limit']) {
                break;
            }

            if (!isset($hit['_source'])
                || !isset($hit['_source']['autocomplete'])
                || !is_array($hit['_source']['autocomplete'])) {
                continue;
            }

            $item = [
                'title'       => null,
                'url'         => null,
                'sku'         => null,
                'image'       => null,
                'description' => null,
                'price'       => null,
                'rating'      => null,
            ];

            $item = array_merge($item, $hit['_source']['autocomplete']);
            if (!isset($item['name']) || !$item['name']) {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }
}

$result = (new ElasticAutocomplete($config, new En, new Nl, new Ru))->process();

//s start
exit(\Zend_Json::encode($result));
//s end
/** m start
return \Zend_Json::encode($result);
m end */
// @codingStandardsIgnoreEnd
