<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\GuestCreateRequestInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateGuestCreateRequestTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Request\ResourceModel\GuestCreateRequest::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Guest Create Request Table'
            )->addColumn(
                GuestCreateRequestInterface::CREATE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                GuestCreateRequestInterface::SECRET_CODE,
                Table::TYPE_TEXT,
                32,
                [
                    'nullable' => false
                ]
            )->addColumn(
                GuestCreateRequestInterface::ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            );
    }
}
