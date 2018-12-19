<?php

namespace Fecon\OrsProducts\Model\Ors;

/**
 * Class to create Ors attribute set and attribute group
 */
class AttributeSet extends \Fecon\OrsProducts\Model\AbstractAttributeSet
{

    /**
     * Get attribute set names to be created
     *
     * @return array
     */
    protected function getAttributeSetNames()
    {
        return [
            'ORS Products' => [
                'ORS Atributes' => [
                    'unspsc',
                    'upc',
                    'mfg_part_number',
                    'web_uom',
                    'family',
                    'manufacturer',
                    'manufacturer_url',
                    'manufacturer_logo',
                    'hazmat',
                    'testing_and_approvals',
                    'minimum_order',
                    'standard_pack',
                    'prop_65_warning_required',
                    'prop_65_warning_label',
                    'prop_65_warning_message',
                    'features',
                    'attributes'
                ]
            ]
        ];
    }
}