<?php

namespace Fecon\OrsProducts\Model\Ors;

/**
 * Class to create Ors attributes
 */
class Attributes extends \Fecon\OrsProducts\Model\AbstractAttributes
{

    /**
     * Get Text Attributes
     *
     * @return array
     */
    protected function getTextAttributes()
    {
        $orsAttributes = [
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
        $attributes = $this->mergeTextAttributes($orsAttributes);


        return $attributes;
    }

    /**
     * Get user defined dropdown attributes
     *
     * @return array
     */
    protected function getDropdownAttributes()
    {
        $orsAttributes = [
            'web_uom' => [
                'label' => 'WebUOM',
            ],
            'family' => [
                'label' => 'Family'
            ],
            'manufacturer_url' => [
                'label' => 'Manufacturer URL'
            ],
            'manufacturer_logo' => [
                'label' => 'Manufacturer Logo'
            ],
            'hazmat' => [
                'label' => 'Hazmat',
                'input' => 'boolean'
            ],
            'testing_and_approvals' => [
                'label' => 'TestingAndApprovals'
            ],
            'minimum_order' => [
                'label' => 'MinimumOrder'
            ],
            'standard_pack' => [
                'label' => 'StandardPack'
            ],
            'prop_65_warning_required' => [
                'label' => 'Prop 65 Warning Required',
                'input' => 'boolean'
            ],
            'prop_65_warning_label' => [
                'label' => 'Prop 65 Warning Label',
                'input' => 'boolean'
            ],
            'prop_65_warning_message' => [
                'label' => 'Prop 65 Warning Message'
            ]
        ];

        $attributes = $this->mergeDropdownAttributes($orsAttributes);

        return $attributes;
    }

    /**
     * Get user defined multiselect attributes
     *
     * @return array
     */
    protected function getMultiselectAttributes()
    {
        $orsAttributes = [
            'features' => [
                'label' => 'Features',
            ]
        ];

        $attributes = $this->mergeMultiselectAttributes($orsAttributes);

        return $attributes;
    }

    /**
     * Get user defined textarea attributes
     *
     * @return array
     */
    protected function getTextareaAttributes()
    {
        $orsAttributes = [
            'attributes' => [
                'label' => 'Attributes',
            ]
        ];

        $attributes = $this->mergeTextareaAttributes($orsAttributes);

        return $attributes;
    }
}