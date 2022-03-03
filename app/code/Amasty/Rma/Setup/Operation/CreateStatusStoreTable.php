<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\StatusInterface;
use Amasty\Rma\Api\Data\StatusStoreInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateStatusStoreTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Status\ResourceModel\StatusStore::TABLE_NAME);
        $statusTable = $setup->getTable(\Amasty\Rma\Model\Status\ResourceModel\Status::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Status Stores Table'
            )->addColumn(
                StatusStoreInterface::STATUS_STORE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                StatusStoreInterface::STATUS_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::STORE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::LABEL,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => '', 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => '', 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::SEND_EMAIL_TO_CUSTOMER,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'default' => false, 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::CUSTOMER_EMAIL_TEMPLATE,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::CUSTOMER_CUSTOM_TEXT,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => '', 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::SEND_EMAIL_TO_ADMIN,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'default' => false, 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::ADMIN_EMAIL_TEMPLATE,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::ADMIN_CUSTOM_TEXT,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => '', 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::SEND_TO_CHAT,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'default' => false, 'nullable' => false
                ]
            )->addColumn(
                StatusStoreInterface::CHAT_MESSAGE,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => '', 'nullable' => false
                ]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    StatusStoreInterface::STATUS_ID,
                    $statusTable,
                    StatusInterface::STATUS_ID
                ),
                StatusStoreInterface::STATUS_ID,
                $statusTable,
                StatusInterface::STATUS_ID,
                Table::ACTION_CASCADE
            );
    }
}
