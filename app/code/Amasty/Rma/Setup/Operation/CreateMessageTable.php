<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\MessageInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateMessageTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Chat\ResourceModel\Message::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Message Table'
            )->addColumn(
                MessageInterface::MESSAGE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                MessageInterface::REQUEST_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                MessageInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT
                ]
            )->addColumn(
                MessageInterface::NAME,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false, 'default' => ''
                ]
            )->addColumn(
                MessageInterface::MESSAGE,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ]
            )->addColumn(
                MessageInterface::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                MessageInterface::MANAGER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                MessageInterface::IS_SYSTEM,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => 0
                ]
            )->addColumn(
                MessageInterface::IS_MANAGER,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => 0
                ]
            )->addColumn(
                MessageInterface::IS_READ,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => 0
                ]
            );
    }
}
