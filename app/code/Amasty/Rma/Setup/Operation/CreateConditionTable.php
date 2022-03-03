<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\ConditionInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateConditionTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Condition\ResourceModel\Condition::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Item Conditions Table'
            )->addColumn(
                ConditionInterface::CONDITION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                ConditionInterface::TITLE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false, 'default' => ''
                ]
            )->addColumn(
                ConditionInterface::STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                ConditionInterface::POSITION,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                ConditionInterface::IS_DELETED,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => false
                ]
            );
    }
}
