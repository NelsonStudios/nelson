<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\ReturnRulesCustomerGroupsInterface;
use Amasty\Rma\Api\Data\ReturnRulesInterface;
use Amasty\Rma\Model\ReturnRules\ResourceModel\ReturnRules;
use Amasty\Rma\Model\ReturnRules\ResourceModel\ReturnRulesCustomerGroups;
use Magento\Customer\Model\Group;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateReturnRulesCustomerGroupsTable
{
    /**
     * @var ProductMetadataInterface
     */
    private $metadata;

    public function __construct(ProductMetadataInterface $metadata)
    {
        $this->metadata = $metadata;
    }

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
        $mainTable = $setup->getTable(ReturnRulesCustomerGroups::TABLE_NAME);
        $rulesTable = $setup->getTable(ReturnRules::TABLE_NAME);
        $groupsTable = $setup->getTable(Group::ENTITY);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Rma return rules customer groups table'
            )->addColumn(
                ReturnRulesCustomerGroupsInterface::RULE_CUSTOMER_GROUP_ID,
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
                ReturnRulesCustomerGroupsInterface::CUSTOMER_GROUP_ID,
                version_compare($this->metadata->getVersion(), '2.2.0', '<')
                    ? Table::TYPE_SMALLINT
                    : Table::TYPE_INTEGER,
                version_compare($this->metadata->getVersion(), '2.2.0', '<')
                    ? 5
                    : 10,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Customer Group ID'
            )->addColumn(
                ReturnRulesCustomerGroupsInterface::RULE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Rule ID'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    ReturnRulesCustomerGroupsInterface::CUSTOMER_GROUP_ID,
                    $groupsTable,
                    'customer_group_id'
                ),
                ReturnRulesCustomerGroupsInterface::CUSTOMER_GROUP_ID,
                $groupsTable,
                'customer_group_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    ReturnRulesCustomerGroupsInterface::RULE_ID,
                    $rulesTable,
                    ReturnRulesInterface::ID
                ),
                ReturnRulesCustomerGroupsInterface::RULE_ID,
                $rulesTable,
                ReturnRulesInterface::ID,
                Table::ACTION_CASCADE
            );
    }
}
