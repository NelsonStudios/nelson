<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\RequestItemInterface;
use Amasty\Rma\Api\Data\RequestInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateRequestItemTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Request\ResourceModel\RequestItem::TABLE_NAME);
        $requestTable = $setup->getTable(\Amasty\Rma\Model\Request\ResourceModel\Request::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Request Items Table'
            )->addColumn(
                RequestItemInterface::REQUEST_ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                RequestItemInterface::REQUEST_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestItemInterface::ORDER_ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestItemInterface::QTY,
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000']
            )->addColumn(
                RequestItemInterface::REQUEST_QTY,
                Table::TYPE_DECIMAL,
                '12,4',
                ['default' => '0.0000']
            )->addColumn(
                RequestItemInterface::REASON_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestItemInterface::CONDITION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestItemInterface::RESOLUTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestItemInterface::ITEM_STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'default' => 0
                ]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    RequestItemInterface::REQUEST_ID,
                    $requestTable,
                    RequestInterface::REQUEST_ID
                ),
                RequestItemInterface::REQUEST_ID,
                $requestTable,
                RequestInterface::REQUEST_ID,
                Table::ACTION_CASCADE
            );
    }
}
