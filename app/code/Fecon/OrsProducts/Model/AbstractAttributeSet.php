<?php

namespace Fecon\OrsProducts\Model;

use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;

/**
 * Abstract class with basic functionality to create new Attribute Set and Attribute Group
 */
abstract class AbstractAttributeSet
{

    /**
     * @var AttributeSetFactory 
     */
    protected $attributeSetFactory;

    /**
     * Constructor
     *
     * @param AttributeSetFactory $attributeSetFactory
     * @return void
     */
    public function __construct(AttributeSetFactory $attributeSetFactory)
    {
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * Generate attribute set data based on attribute set name
     *
     * @param string $attributeSetName
     * @param EavSetup $eavSetup
     * @return array
     */
    protected function getAttributeSetData($attributeSetName, $eavSetup)
    {
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $data = [
            'attribute_set_name'    => $attributeSetName,
            'entity_type_id'        => $entityTypeId
        ];

        return $data;
    }

    /**
     * Create an attribute set
     *
     * @param string $attributeSetName
     * @param EavSetup $eavSetup
     * @return \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected function createAttributeSet($attributeSetName, EavSetup $eavSetup)
    {
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $attributeSetData = $this->getAttributeSetData($attributeSetName, $eavSetup);
        $attributeSet = $this->attributeSetFactory->create();
        $attributeSet->setData($attributeSetData);
        $attributeSet->validate();
        $attributeSet->save();
        $attributeSet->initFromSkeleton($attributeSetId);
        $attributeSet->save();

        return $attributeSet;
    }

    /**
     * Create an attribute group
     *
     * @param EavSetup $eavSetup
     * @param string $groupName
     * @param int $attributeSetId
     * @param array $attributes
     */
    protected function createAttributeGroup(EavSetup $eavSetup, $groupName, $attributeSetId, $attributes)
    {
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName);
        $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
        // Add existing attributes to group
        foreach ($attributes as $attribute) {
            $attributeId = $eavSetup->getAttributeId($entityTypeId, $attribute);
            $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId);
        }
    }

    /**
     * Create Ors structure for attributes
     *
     * @param EavSetup $eavSetup
     */
    public function createStructure($eavSetup)
    {
        $attributeSets = $this->getAttributeSetNames();
        foreach ($attributeSets as $attributeSet => $groups) {
            $attributeSet = $this->createAttributeSet($attributeSet, $eavSetup);
            $attributeSetId = $attributeSet->getId();
            foreach ($groups as $group => $attributes) {
                $this->createAttributeGroup($eavSetup, $group, $attributeSetId, $attributes);
            }
        }
    }

    /**
     * Get attribute set names to be created
     *
     * @return array
     */
    abstract protected function getAttributeSetNames();
}