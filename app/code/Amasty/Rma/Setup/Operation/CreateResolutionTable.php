<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\ResolutionInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateResolutionTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Resolution\ResourceModel\Resolution::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Resolutions Table'
            )->addColumn(
                ResolutionInterface::RESOLUTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                ResolutionInterface::TITLE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false, 'default' => ''
                ]
            )->addColumn(
                ResolutionInterface::STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                ResolutionInterface::POSITION,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'nullable' => false
                ]
            )->addColumn(
                ResolutionInterface::IS_DELETED,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => false
                ]
            );
    }
}
