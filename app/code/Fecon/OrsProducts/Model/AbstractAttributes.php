<?php

namespace Fecon\OrsProducts\Model;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;

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
     * Merge text attributes with all options defined
     *
     * @param array $userDefinedAttributes
     * @return array
     */
    protected function mergeTextAttributes($userDefinedAttributes)
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
     * Merge Dropdown attributes with all options defined
     *
     * @param array $userDefinedAttributes
     * @return array
     */
    protected function mergeMultiselectAttributes($userDefinedAttributes)
    {
        $defaultDropdownValues = $this->getDefaultDropdownValues();

        return $this->mergeOptions($defaultDropdownValues, $userDefinedAttributes);
    }

    /**
     * Merge multiselect attributes with all options defined
     *
     * @param array $userDefinedAttributes
     * @return array
     */
    protected function mergeDropdownAttributes($userDefinedAttributes)
    {
        $defaultDropdownValues = $this->getDefaultMultiselectValues();

        return $this->mergeOptions($defaultDropdownValues, $userDefinedAttributes);
    }

    /**
     * Merge textarea attributes with all options defined
     *
     * @param array $userDefinedAttributes
     * @return array
     */
    protected function mergeTextareaAttributes($userDefinedAttributes)
    {
        $defaultTextValues = $this->getDefaultTextareaValues();

        return $this->mergeOptions($defaultTextValues, $userDefinedAttributes);
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

    /**
     * Get Default options for multiselect attributes
     *
     * @return array
     */
    protected function getDefaultMultiselectValues()
    {
        return [
            'type' => 'varchar',
            'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'frontend' => '',
            'input' => 'multiselect',
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
     * Get Default options for text attributes
     *
     * @return array
     */
    protected function getDefaultTextareaValues()
    {
        return [
            'type' => 'text',
            'frontend' => '',
            'input' => 'textarea',
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
     * Create attributes for Ors products
     *
     * @param EavSetup $eavSetup
     */
    public function createAttributes($eavSetup)
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
        $textAttributes = $this->getTextAttributes();
        $dropdownAttributes = $this->getDropdownAttributes();
        $multiselectAttributes = $this->getMultiselectAttributes();
        $textareaAttributes = $this->getTextareaAttributes();

        $attributes = array_merge($textAttributes, $dropdownAttributes);
        $attributes = array_merge($attributes, $multiselectAttributes);
        $attributes = array_merge($attributes, $textareaAttributes);
        return $attributes;
    }

    /**
     * Get Text Attributes
     *
     * @return array
     */
    abstract protected function getTextAttributes();

    /**
     * Get user defined dropdown attributes
     *
     * @return array
     */
    abstract protected function getDropdownAttributes();

    /**
     * Get user defined multiselect attributes
     *
     * @return array
     */
    abstract protected function getMultiselectAttributes();

    /**
     * Get user defined textarea attributes
     *
     * @return array
     */
    abstract protected function getTextareaAttributes();
}