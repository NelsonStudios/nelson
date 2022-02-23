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



namespace Mirasvit\Search\Plugin;

use Magento\Search\Model\QueryFactory;
use Mirasvit\Search\Block\Result;
use Mirasvit\Search\Model\Config;

class HighlightPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * HighlightPlugin constructor.
     *
     * @param Config       $config
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        Config $config,
        QueryFactory $queryFactory
    ) {
        $this->config       = $config;
        $this->queryFactory = $queryFactory;
    }

    /**
     * @param Result $block
     * @param string $html
     *
     * @return string
     * @SuppressWarnings(PHPMD)
     */
    public function afterToHtml(
        Result $block,
        $html
    ) {
        if (!$this->config->isHighlightingEnabled()) {
            return $html;
        }

        $html = $this->highlight(
            $html,
            $this->queryFactory->get()->getQueryText()
        );

        return $html;
    }

    /**
     * @param string $html
     * @param string $query
     *
     * @return string
     */
    public function highlight($html, $query)
    {
        if (strlen($query) < 3) {
            return $html;
        }

        $query = $this->removeSpecialChars($query);
        $queryWithHolders = [];
        $preparedQuery = array_filter(explode(' ', $query));
        usort($preparedQuery, function ($a, $b) {
            return strlen($a) - strlen($b);
        });

        foreach ($preparedQuery as $subQuery) {
            $queryWithHolders[$subQuery] = '{highlightStart}' .$subQuery. '{highlightEnd}';
        }

        $result = preg_match_all('/>[\w\d\s\S][^<>]*['. implode('|', explode(' ', $query)) .']+[\w\d\s\S][^<>]*<\/a>/iU', $html, $matches);

        foreach ($matches[0] as $key => $match) {
            $strippedMatch = strip_tags($match);
            $replacement = $strippedMatch;
            foreach ($preparedQuery as $subQuery) {
                $replacement = preg_replace(
                    '/(' . $subQuery . ')+/i',
                    '{?&&?}$1{!&&!}',
                    $replacement
                );
            }

            if ($strippedMatch != $replacement) {
                $html = str_ireplace($strippedMatch, $replacement, $html);
            }
        }
        $html = str_ireplace(['{?&&?}', '{!&&!}'], ['<span class="mst-search__highlight">', '</span>'], $html);

        return $html;
    }

    /**
     * @param string $query
     *
     * @return string
     */
    public function removeSpecialChars($query)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\||\\\)/';
        $replace = ' ';

        return preg_replace($pattern, $replace, $query);
    }
}
