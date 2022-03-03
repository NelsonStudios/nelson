<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\HistoryInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateHistoryTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\History\ResourceModel\History::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA History Table'
            )->addColumn(
                HistoryInterface::EVENT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                HistoryInterface::REQUEST_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                HistoryInterface::EVENT_DATE,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT
                ]
            )->addColumn(
                HistoryInterface::EVENT_TYPE,
                Table::TYPE_SMALLINT,
                255,
                [
                    'nullable' => false, 'default' => 0
                ]
            )->addColumn(
                HistoryInterface::EVENT_DATA,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ]
            )->addColumn(
                HistoryInterface::EVENT_INITIATOR,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'unsigned' => true, 'default' => 0
                ]
            )->addColumn(
                HistoryInterface::EVENT_INITIATOR_NAME,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false, 'default' => ''
                ]
            );
    }
}
