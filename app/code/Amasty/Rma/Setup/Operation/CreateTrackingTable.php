<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\TrackingInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateTrackingTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Request\ResourceModel\Tracking::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Tracking Number Table'
            )->addColumn(
                TrackingInterface::TRACKING_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                TrackingInterface::REQUEST_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                TrackingInterface::TRACKING_CODE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ]
            )->addColumn(
                TrackingInterface::TRACKING_NUMBER,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ]
            )->addColumn(
                TrackingInterface::IS_CUSTOMER,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => true
                ]
            );
    }
}
