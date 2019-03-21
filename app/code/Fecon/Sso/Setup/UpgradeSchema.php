<?php


namespace Fecon\Sso\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            $tableSsoOrganization = $setup->getConnection()->newTable($setup->getTable('fecon_sso_organization'));

            $tableSsoOrganization->addColumn(
                'organization_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
                'Entity ID'
            );

            $tableSsoOrganization->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'name'
            );

            $tableSsoUserGroup = $setup->getConnection()->newTable($setup->getTable('fecon_sso_usergroup'));

            $tableSsoUserGroup->addColumn(
                'usergroup_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,],
                'Entity ID'
            );

            $tableSsoUserGroup->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'name'
            );

            $setup->getConnection()->createTable($tableSsoUserGroup);

            $setup->getConnection()->createTable($tableSsoOrganization);
        }
    }
}
