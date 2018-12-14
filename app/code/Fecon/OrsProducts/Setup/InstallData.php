<?php

namespace Fecon\OrsProducts\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{

    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
    ModuleDataSetupInterface $setup, ModuleContextInterface $context
    )
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
        \Magento\Catalog\Model\Product::ENTITY,
        'unspsc',
        [
        'type' => 'varchar',
        'backend' => '',
        'frontend' => '',
        'label' => 'UNSPSC',
        'input' => 'text',
        'class' => '',
        'source' => '',
        'global' => 1,
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
        'group' => 'General',
        'option' => array('values' => array(""))
        ]
        );

        $eavSetup->addAttribute(
        \Magento\Catalog\Model\Product::ENTITY,
        'upc',
        [
        'type' => 'varchar',
        'backend' => '',
        'frontend' => '',
        'label' => 'UPC',
        'input' => 'text',
        'class' => '',
        'source' =>,
        'global' => 1,
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
        'group' => 'General',
        'option' => array('values' => array(""))
        ]
        );

        $eavSetup->addAttribute(
        \Magento\Catalog\Model\Product::ENTITY,
        'mfg_part_number',
        [
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'MfgPartNumber',
        'input' => 'select',
        'class' => '',
        'source' =>,
        'global' => 1,
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
        'group' => 'General',
        'option' => array('values' => array(""))
        ]
        );

        $eavSetup->addAttribute(
        \Magento\Catalog\Model\Product::ENTITY,
        'web_uom',
        [
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'WebUOM',
        'input' => 'select',
        'class' => '',
        'source' =>,
        'global' => 1,
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
        'group' => 'General',
        'option' => array('values' => array(""))
        ]
        );

        $eavSetup->addAttribute(
        \Magento\Catalog\Model\Product::ENTITY,
        'family',
        [
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Family',
        'input' => 'select',
        'class' => '',
        'source' =>,
        'global' => 1,
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
        'group' => 'General',
        'option' => array('values' => array(""))
        ]
        );

        $eavSetup->addAttribute(
        \Magento\Catalog\Model\Product::ENTITY,
        'manufacturer_uRL',
        [
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'Manufacturer URL',
        'input' => 'select',
        'class' => '',
        'source' =>,
        'global' => 1,
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
        'group' => 'General',
        'option' => array('values' => array(""))
        ]
        );
    }
}