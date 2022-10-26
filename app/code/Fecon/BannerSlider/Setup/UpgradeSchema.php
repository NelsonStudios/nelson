<?php

namespace Fecon\BannerSlider\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    const CATEGORIES = 'categories';
    const PRODUCTS = 'products';

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addCategoriesAndProductColumn($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addCategoriesAndProductColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('mageplaza_bannerslider_slider'),
            self::CATEGORIES,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Categories'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('mageplaza_bannerslider_slider'),
            self::PRODUCTS,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Products'
        );
    }
}
