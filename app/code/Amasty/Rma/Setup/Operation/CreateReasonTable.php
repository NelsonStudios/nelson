<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\ReasonInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateReasonTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Reason\ResourceModel\Reason::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Return Reasons Table'
            )->addColumn(
                ReasonInterface::REASON_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                ReasonInterface::TITLE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false, 'default' => ''
                ]
            )->addColumn(
                ReasonInterface::PAYER,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                ReasonInterface::STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                ReasonInterface::POSITION,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                ReasonInterface::IS_DELETED,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => false
                ]
            );
    }
}
