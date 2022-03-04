<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\ResolutionInterface;
use Amasty\Rma\Api\Data\ReturnRulesInterface;
use Amasty\Rma\Api\Data\ReturnRulesResolutionsInterface;
use Amasty\Rma\Model\Resolution\ResourceModel\Resolution;
use Amasty\Rma\Model\ReturnRules\ResourceModel\ReturnRules;
use Amasty\Rma\Model\ReturnRules\ResourceModel\ReturnRulesResolutions;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateReturnRulesResolutionsTable
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
        $mainTable = $setup->getTable(ReturnRulesResolutions::TABLE_NAME);
        $resolutionsTable = $setup->getTable(Resolution::TABLE_NAME);
        $rulesTable = $setup->getTable(ReturnRules::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Rma return rules resolutions table'
            )->addColumn(
                ReturnRulesResolutionsInterface::RULE_RESOLUTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Entity ID'
            )->addColumn(
                ReturnRulesResolutionsInterface::RULE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Rule ID'
            )->addColumn(
                ReturnRulesResolutionsInterface::RESOLUTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Resolution ID'
            )->addColumn(
                ReturnRulesResolutionsInterface::VALUE,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Resolution Value'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    ReturnRulesResolutionsInterface::RESOLUTION_ID,
                    $resolutionsTable,
                    ResolutionInterface::RESOLUTION_ID
                ),
                ReturnRulesResolutionsInterface::RESOLUTION_ID,
                $resolutionsTable,
                ResolutionInterface::RESOLUTION_ID,
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    ReturnRulesResolutionsInterface::RULE_ID,
                    $rulesTable,
                    ReturnRulesInterface::ID
                ),
                ReturnRulesResolutionsInterface::RULE_ID,
                $rulesTable,
                ReturnRulesInterface::ID,
                Table::ACTION_CASCADE
            );
    }
}
