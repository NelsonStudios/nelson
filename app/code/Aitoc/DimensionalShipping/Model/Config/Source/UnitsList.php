<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Model\Config\Source;

class UnitsList implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'mm', 'label' => __('mm')],
            ['value' => 'cm', 'label' => __('cm')],
            ['value' => 'in', 'label' => __('in')],
            ['value' => 'ft', 'label' => __('ft')],
        ];
    }
}
