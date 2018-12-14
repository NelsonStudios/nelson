<?php

namespace Fecon\OrsProducts\Model\Abrasive;

/**
 * Description of Attributes
 */
class Attributes extends \Fecon\OrsProducts\Model\AbstractAttributes
{

    protected function getAbrasiveTextAttributes()
    {
        $abrasiveAttributes = [
            'unspsc' => [
                'label' => 'UNSPSC',
                'group' => 'General',
            ]
        ];
        $attributes = $this->getTextAttributes($abrasiveAttributes);


        return $attributes;
    }
}