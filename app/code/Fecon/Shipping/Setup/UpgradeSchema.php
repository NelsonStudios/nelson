<?php

namespace Fecon\Shipping\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * UpgradeSchema class
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            $installer = $setup;
            $installer->startSetup();
            $eavTable = $installer->getTable('fecon_shipping_preorder');
            $columns = [
                'status' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'comment' => 'Preorder Status',
                    'unsigned' => true,
                    'default' => 1
                ],
                'cart_data' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'Cart Data',
                    'LENGTH' => '2M'
                ]

            ];

            $connection = $installer->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($eavTable, $name, $definition);
            }

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), "1.0.2", "<")) {
            $installer = $setup;
            $installer->startSetup();
            $eavTable = $installer->getTable('fecon_shipping_preorder');
            $columns = [
                'comments' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Comments',
                    'LENGTH' => '2M'
                ]

            ];

            $connection = $installer->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($eavTable, $name, $definition);
            }

            $installer->endSetup();
        }
    }
}