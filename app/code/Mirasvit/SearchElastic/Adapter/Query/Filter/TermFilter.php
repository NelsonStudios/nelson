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



namespace Mirasvit\SearchElastic\Adapter\Query\Filter;

use Magento\Framework\Search\Request\Filter\Term;

class TermFilter
{
    /**
     * @param Term $filter
     *
     * @return array
     */
    public function build(Term $filter)
    {
        $query = [];

        if ($filter->getValue() !== null) {
            $value = $filter->getValue();

            if (is_string($value)) {
                if ($filter->getValue() !== "0") {
                    $value = array_filter(explode(',', $value));
                } else {
                    $value = [0];
                }

                if (count($value) === 1) {
                    $value = $value[0];
                }

                $value = preg_replace('/[^A-Za-z0-9 -]/', '', $value);
            }


            $condition = is_array($value) ? 'terms' : 'term';

            if (is_array($value)) {
                if (key_exists('in', $value)) {
                    $value = $value['in'];
                }

                if (key_exists('finset', $value)) {
                    $value = $value['finset'];
                }

                $value = array_values($value);
            }

            $field = $filter->getField() . '_raw';

            if ($field == 'entity_id_raw') {
                $field = 'entity_id';
            }

            if ($field == '_id_raw') {
                $field = '_id';
            }

            if (is_array($value) && is_array($value[0])) {
                $value = $value[0];
            }

            $query[] = [
                $condition => [
                    $field => $value,
                ],
            ];
        }

        return $query;
    }
}
