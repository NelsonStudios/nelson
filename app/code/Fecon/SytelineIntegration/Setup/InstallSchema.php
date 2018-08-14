<?php


namespace Fecon\SytelineIntegration\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $table_fecon_sytelineintegration_submission = $setup->getConnection()->newTable($setup->getTable('fecon_sytelineintegration_submission'));

        
        $table_fecon_sytelineintegration_submission->addColumn(
            'submission_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            array('identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,),
            'Entity ID'
        );
        

        
        $table_fecon_sytelineintegration_submission->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false,'unsigned' => true],
            'order_id'
        );
        

        
        $table_fecon_sytelineintegration_submission->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'created_at'
        );
        

        
        $table_fecon_sytelineintegration_submission->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'updated_at'
        );
        

        
        $table_fecon_sytelineintegration_submission->addColumn(
            'success',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'success'
        );
        

        
        $table_fecon_sytelineintegration_submission->addColumn(
            'testing',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'testing'
        );
        

        
        $table_fecon_sytelineintegration_submission->addColumn(
            'request',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'request'
        );
        

        
        $table_fecon_sytelineintegration_submission->addColumn(
            'attempts',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['default' => '1','nullable' => false,'unsigned' => true],
            'attempts'
        );
        

        
        $table_fecon_sytelineintegration_submission->addColumn(
            'response',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'response'
        );
        

        
        $table_fecon_sytelineintegration_submission->addColumn(
            'errors',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [],
            'errors'
        );
        

        $setup->getConnection()->createTable($table_fecon_sytelineintegration_submission);

        $setup->endSetup();
    }
}
