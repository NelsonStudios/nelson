<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->updateToDecimal($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function updateToDecimal(SchemaSetupInterface $setup)
    {
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $setup->getConnection();
        $tableColumns = [
            'aitoc_dimensional_shipping_boxes' => [
                'weight',
                'empty_weight',
                'width',
                'height',
                'length',
                'outer_width',
                'outer_height',
                'outer_length'
            ],
            'aitoc_dimensional_shipping_product_attributes' => [
                'width',
                'height',
                'length'
            ],
            'aitoc_dimensional_shipping_order_boxes' => [
                'weight'
            ]
        ];
        foreach ($tableColumns as $table => $columns) {
            $tableName = $setup->getTable($table);
            if ($connection->isTableExists($tableName)) {
                foreach ($columns as $column) {
                    $connection->changeColumn(
                        $tableName,
                        $column,
                        $column,
                        ['type' => Table::TYPE_DECIMAL, 'length' => '12,3']
                    );
                }
            }
        }
    }
}
