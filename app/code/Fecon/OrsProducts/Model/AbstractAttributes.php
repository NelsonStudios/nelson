<?php

namespace Fecon\OrsProducts\Model;

/**
 * Description of AbstractAttributes
 */
abstract class AbstractAttributes
{

    protected function getDefaultTextValues()
    {
        return [
            'type' => 'varchar',
            'frontend' => '',
            'input' => 'text',
            'class' => '',
            'source' => '',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'default' => null,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => false,
            'unique' => false,
            'apply_to' => '',
            'system' => 1,
        ];
    }

    protected function getTextAttributes($userDefinedAttributes)
    {
//        $attributes = [
//            'unspsc' => [
//                'label' => 'UNSPSC',
//                'group' => 'General',
//            ]
//        ];
        $attributes = [];
        $defaultTextValues = $this->getDefaultTextValues();

        foreach ($userDefinedAttributes as $attributeCode => $values) {
            $attributeValues = array_merge($defaultTextValues, $values);
            $attributes[$attributeCode] = $attributeValues;
        }

        return $attributes;
    }
}