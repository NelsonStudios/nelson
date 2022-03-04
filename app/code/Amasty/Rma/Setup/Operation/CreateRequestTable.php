<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\RequestInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateRequestTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Request\ResourceModel\Request::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Request Table'
            )->addColumn(
                RequestInterface::REQUEST_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                RequestInterface::ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestInterface::STORE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT
                ]
            )->addColumn(
                RequestInterface::MODIFIED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE
                ]
            )->addColumn(
                RequestInterface::STATUS,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestInterface::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestInterface::CUSTOMER_NAME,
                Table::TYPE_TEXT,
                512,
                [
                    'nullable' => false, 'default' => ''
                ]
            )->addColumn(
                RequestInterface::URL_HASH,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestInterface::MANAGER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestInterface::CUSTOM_FIELDS,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ]
            )->addColumn(
                RequestInterface::RATING,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                RequestInterface::RATING_COMMENT,
                Table::TYPE_TEXT,
                null
            )->addColumn(
                RequestInterface::NOTE,
                Table::TYPE_TEXT,
                null
            )->addColumn(
                RequestInterface::SHIPPING_LABEL,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true, 'unsigned' => true
                ]
            );
    }
}
