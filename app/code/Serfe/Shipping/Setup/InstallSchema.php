<?php

namespace Serfe\Shipping\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * InstallSchema class
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $tableSerfeShippingPreorder = $setup->getConnection()->newTable($setup->getTable('serfe_shipping_preorder'));

        
        $tableSerfeShippingPreorder->addColumn(
            'preorder_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            array('identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,),
            'Entity ID'
        );
        

        
        $tableSerfeShippingPreorder->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'created_at'
        );
        

        
        $tableSerfeShippingPreorder->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'updated_at'
        );
        

        
        $tableSerfeShippingPreorder->addColumn(
            'is_available',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'is_available'
        );
        

        
        $tableSerfeShippingPreorder->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false,'unsigned' => true],
            'customer_id'
        );
        

        
        $tableSerfeShippingPreorder->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false,'unsigned' => true],
            'quote_id'
        );
        

        
        $tableSerfeShippingPreorder->addColumn(
            'address_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false,'unsigned' => true],
            'address_id'
        );
        

        
        $tableSerfeShippingPreorder->addColumn(
            'shipping_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'shipping_method'
        );
        

        
        $tableSerfeShippingPreorder->addColumn(
            'shipping_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            null,
            [],
            'shipping_price'
        );
        

        $setup->getConnection()->createTable($tableSerfeShippingPreorder);

        $setup->endSetup();
    }
}
