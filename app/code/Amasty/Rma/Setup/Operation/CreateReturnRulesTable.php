<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\ReturnRulesInterface;
use Amasty\Rma\Model\ReturnRules\ResourceModel\ReturnRules;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateReturnRulesTable
{
    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
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
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $mainTable = $setup->getTable(ReturnRules::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Rma return rules table'
            )->addColumn(
                ReturnRulesInterface::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'ID'
            )->addColumn(
                ReturnRulesInterface::NAME,
                Table::TYPE_TEXT,
                225,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Return Rule Name'
            )->addColumn(
                ReturnRulesInterface::STATUS,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ],
                'Status'
            )->addColumn(
                ReturnRulesInterface::PRIORITY,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Priority of rule'
            )->addColumn(
                ReturnRulesInterface::DEFAULT_RESOLUTION,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true
                ],
                'Default resolution period'
            )->addColumn(
                ReturnRulesInterface::CONDITIONS_SERIALIZED,
                Table::TYPE_TEXT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Serialized Conditions'
            );
    }
}
