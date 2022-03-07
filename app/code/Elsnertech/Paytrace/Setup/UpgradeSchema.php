<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $table = $setup->getConnection()
            ->newTable($setup->getTable('elsnertech_paytrace_customers'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'customer id'
            )
            ->addColumn(
                'paytrace_customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'paytrace customer id'
            )
            ->addColumn(
                'cc_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'number'
            )
            ->addColumn(
                'cc_year',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                5,
                ['nullable' => true],
                'Year'
            )
            ->addColumn(
                'cc_month',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                5,
                ['nullable' => true],
                'Month'
            )
            ->addColumn(
                'cc_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => true],
                'Card Type'
            )
            ->setComment("elsnertech_paytrace_customers");
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
