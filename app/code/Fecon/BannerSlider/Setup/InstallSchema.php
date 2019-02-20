<?php

namespace Fecon\BannerSlider\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

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
        $table = $installer->getTable('mageplaza_bannerslider_banner');
        $columns = [
            'subtitle' => [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Subtitle',
            ],
            'image_description' => [
                'type' => Table::TYPE_TEXT,
                'LENGTH' => '64k',
                'nullable' => true,
                'comment' => 'Content',
            ],
            'cta_text' => [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'CTA Text',
            ],
            'cta_link' => [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'CTA Link',
            ],
            'cta_target' => [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => true,
                'comment' => 'Open new tab',
                'default' => '1'
            ],
        ];

        $connection = $installer->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($table, $name, $definition);
        }
    }
}
