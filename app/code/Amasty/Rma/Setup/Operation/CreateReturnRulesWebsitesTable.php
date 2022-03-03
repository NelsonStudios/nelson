<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\ReturnRulesInterface;
use Amasty\Rma\Api\Data\ReturnRulesWebsitesInterface;
use Amasty\Rma\Model\ReturnRules\ResourceModel\ReturnRules;
use Amasty\Rma\Model\ReturnRules\ResourceModel\ReturnRulesWebsites;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateReturnRulesWebsitesTable
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
    private function createTable($setup)
    {
        $mainTable = $setup->getTable(ReturnRulesWebsites::TABLE_NAME);
        $websitesTable = $setup->getTable('store_website');
        $rulesTable = $setup->getTable(ReturnRules::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Rma return rules websites table'
            )->addColumn(
                ReturnRulesWebsitesInterface::RULE_WEBSITE_ID,
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
                ReturnRulesWebsitesInterface::WEBSITE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true
                ],
                'Website ID'
            )->addColumn(
                ReturnRulesWebsitesInterface::RULE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true
                ],
                'Rule ID'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    ReturnRulesWebsitesInterface::WEBSITE_ID,
                    $websitesTable,
                    'website_id'
                ),
                ReturnRulesWebsitesInterface::WEBSITE_ID,
                $websitesTable,
                'website_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    ReturnRulesWebsitesInterface::RULE_ID,
                    $rulesTable,
                    ReturnRulesInterface::ID
                ),
                ReturnRulesWebsitesInterface::RULE_ID,
                $rulesTable,
                ReturnRulesInterface::ID,
                Table::ACTION_CASCADE
            );
    }
}
