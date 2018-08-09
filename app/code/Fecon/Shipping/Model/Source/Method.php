<?php

namespace Fecon\Shipping\Model\Source;

/**
 * Method class
 *
 * 
 */
class Method implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Returns array to be used in multiselect on back-end
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $configData = [
            'BEST' => 'Best Way to Ship',
            'FREC' => 'Freight Carrier Cheapest',
            'FREQ' => 'Freight Carrier Quickest',
            'OC' => 'Ocean Container'
        ];
        $arr = [];
        foreach ($configData as $code => $title) {
            $arr[] = ['value' => $code, 'label' => $title];
        }
        return $arr;
    }

}