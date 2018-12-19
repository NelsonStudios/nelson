<?php

namespace Fecon\OrsProducts\Model\Abrasive;

/**
 * Description of AttributeSet
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
            'abrasive' => [
                'Abrasives Atributes' => [
                    'unspsc',
                    'upc',
                    'mfg_part_number',
                    'web_uom',
                    'family',
                    'manufacturer_url'
                ]
            ]
        ];
    }
}