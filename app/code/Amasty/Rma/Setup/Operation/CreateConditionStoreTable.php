<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\ConditionInterface;
use Amasty\Rma\Api\Data\ConditionStoreInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateConditionStoreTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(\Amasty\Rma\Model\Condition\ResourceModel\ConditionStore::TABLE_NAME);
        $conditionTable = $setup->getTable(\Amasty\Rma\Model\Condition\ResourceModel\Condition::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Item Condition Stores Table'
            )->addColumn(
                ConditionStoreInterface::CONDITION_STORE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                ConditionStoreInterface::CONDITION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ]
            )->addColumn(
                ConditionStoreInterface::STORE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ]
            )->addColumn(
                ConditionStoreInterface::LABEL,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => '', 'nullable' => false
                ]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    ConditionStoreInterface::CONDITION_ID,
                    $conditionTable,
                    ConditionInterface::CONDITION_ID
                ),
                ConditionStoreInterface::CONDITION_ID,
                $conditionTable,
                ConditionInterface::CONDITION_ID,
                Table::ACTION_CASCADE
            );
    }
}
