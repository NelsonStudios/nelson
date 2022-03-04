<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\StatusInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateStatusTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Status\ResourceModel\Status::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Status Table'
            )->addColumn(
                StatusInterface::STATUS_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                StatusInterface::TITLE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false, 'default' => ''
                ]
            )->addColumn(
                StatusInterface::IS_ENABLED,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'default' => true, 'nullable' => false
                ]
            )->addColumn(
                StatusInterface::IS_INITIAL,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'default' => false, 'nullable' => false
                ]
            )->addColumn(
                StatusInterface::AUTO_EVENT,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => true
                ]
            )->addColumn(
                StatusInterface::STATE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                StatusInterface::GRID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                StatusInterface::PRIORITY,
                Table::TYPE_INTEGER,
                null,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                StatusInterface::COLOR,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                StatusInterface::IS_DELETED,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => false
                ]
            );
    }
}
