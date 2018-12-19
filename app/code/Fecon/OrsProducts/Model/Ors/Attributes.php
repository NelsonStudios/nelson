<?php

namespace Fecon\OrsProducts\Model\Abrasive;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;

/**
 * Class to create Abrasive attributes
 */
class Attributes extends \Fecon\OrsProducts\Model\AbstractAttributes
{

    /**
     * Get Text Attributes
     *
     * @return array
     */
    protected function getAbrasiveTextAttributes()
    {
        $abrasiveAttributes = [
            'unspsc' => [
                'label' => 'UNSPSC'
            ],
            'upc' => [
                'label' => 'UPC'
            ],
            'mfg_part_number' => [
                'label' => 'MfgPartNumber'
            ]
        ];
        $attributes = $this->getTextAttributes($abrasiveAttributes);


        return $attributes;
    }

    /**
     * Get user defined dropdown attributes
     *
     * @return array
     */
    protected function getAbrasiveDropdownAttributes()
    {
        $abrasiveAttributes = [
            'web_uom' => [
                'label' => 'WebUOM',
            ],
            'family' => [
                'label' => 'Family'
            ],
            'manufacturer_url' => [
                'label' => 'Manufacturer URL'
            ]
        ];

        $attributes = $this->getDropdownAttributes($abrasiveAttributes);

        return $attributes;
    }

    /**
     * Create attributes for abrasive products
     *
     * @param EavSetup $eavSetup
     */
    public function createAbrasiveAttributes($eavSetup)
    {
        $attributes = $this->getAttributes();
        foreach ($attributes as $attributeCode => $attributeOptions) {
            $eavSetup->addAttribute(Product::ENTITY, $attributeCode, $attributeOptions);
        }
    }

    /**
     * Get all the attributes
     *
     * @return array
     */
    protected function getAttributes()
    {
        $userDefinedTextAttributes = $this->getAbrasiveTextAttributes();
        $textAttributes = $this->getTextAttributes($userDefinedTextAttributes);
        $userDefinedDropdownAttributes = $this->getAbrasiveDropdownAttributes();
        $dropdownAttributes = $this->getTextAttributes($userDefinedDropdownAttributes);

        $attributes = array_merge($textAttributes, $dropdownAttributes);
        return $attributes;
    }
}