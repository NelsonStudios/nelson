<?php

namespace Fecon\OrsProducts\Model;

/**
 * Abstract class with the logic to handle attributes creation
 */
abstract class AbstractAttributes
{

    /**
     * Get Default options for text attributes
     *
     * @return array
     */
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

    /**
     * Get text attributes with all options defined
     *
     * @param array $userDefinedAttributes
     * @return array
     */
    protected function getTextAttributes($userDefinedAttributes)
    {
        $defaultTextValues = $this->getDefaultTextValues();

        return $this->mergeOptions($defaultTextValues, $userDefinedAttributes);
    }

    /**
     * Merge default and user defined options for attributes
     *
     * @param array $defaultValues
     * @param array $userDefinedAttributes
     * @return array
     */
    protected function mergeOptions($defaultValues, $userDefinedAttributes)
    {
        $attributes = [];

        foreach ($userDefinedAttributes as $attributeCode => $values) {
            $attributeValues = array_merge($defaultValues, $values);
            $attributes[$attributeCode] = $attributeValues;
        }

        return $attributes;
    }

    /**
     * Get Dropdown attributes with all options defined
     *
     * @param array $userDefinedAttributes
     * @return array
     */
    protected function getDropdownAttributes($userDefinedAttributes)
    {
        $defaultDropdownValues = $this->getDefaultDropdownValues();

        return $this->mergeOptions($defaultDropdownValues, $userDefinedAttributes);
    }

    /**
     * Get Default options for dropdown attributes
     *
     * @return array
     */
    protected function getDefaultDropdownValues()
    {
        return [
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'input' => 'select',
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
}