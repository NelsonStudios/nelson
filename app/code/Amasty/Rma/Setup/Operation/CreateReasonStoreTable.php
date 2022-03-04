<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\ReasonInterface;
use Amasty\Rma\Api\Data\ReasonStoreInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateReasonStoreTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Reason\ResourceModel\ReasonStore::TABLE_NAME);
        $reasonTable = $setup->getTable(\Amasty\Rma\Model\Reason\ResourceModel\Reason::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Reason Stores Table'
            )->addColumn(
                ReasonStoreInterface::REASON_STORE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                ReasonStoreInterface::REASON_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ]
            )->addColumn(
                ReasonStoreInterface::STORE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ]
            )->addColumn(
                ReasonStoreInterface::LABEL,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => '', 'nullable' => false
                ]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    ReasonStoreInterface::REASON_ID,
                    $reasonTable,
                    ReasonInterface::REASON_ID
                ),
                ReasonStoreInterface::REASON_ID,
                $reasonTable,
                ReasonInterface::REASON_ID,
                Table::ACTION_CASCADE
            );
    }
}
