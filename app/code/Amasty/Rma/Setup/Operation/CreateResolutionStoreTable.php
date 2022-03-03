<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\ResolutionInterface;
use Amasty\Rma\Api\Data\ResolutionStoreInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateResolutionStoreTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Resolution\ResourceModel\ResolutionStore::TABLE_NAME);
        $resolutionTable = $setup->getTable(\Amasty\Rma\Model\Resolution\ResourceModel\Resolution::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Resolutions Stores Table'
            )->addColumn(
                ResolutionStoreInterface::RESOLUTION_STORE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                ResolutionStoreInterface::RESOLUTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ]
            )->addColumn(
                ResolutionStoreInterface::STORE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ]
            )->addColumn(
                ResolutionStoreInterface::LABEL,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => '', 'nullable' => false
                ]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    ResolutionStoreInterface::RESOLUTION_ID,
                    $resolutionTable,
                    ResolutionInterface::RESOLUTION_ID
                ),
                ResolutionStoreInterface::RESOLUTION_ID,
                $resolutionTable,
                ResolutionInterface::RESOLUTION_ID,
                Table::ACTION_CASCADE
            );
    }
}
