<?php

namespace Amasty\Rma\Setup\Operation;

use Amasty\Rma\Api\Data\MessageFileInterface;
use Amasty\Rma\Api\Data\MessageInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateMessageFileTable
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
        $table = $setup->getTable(\Amasty\Rma\Model\Chat\ResourceModel\MessageFile::TABLE_NAME);
        $messageTable = $setup->getTable(\Amasty\Rma\Model\Chat\ResourceModel\Message::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty RMA Message File Table'
            )->addColumn(
                MessageFileInterface::MESSAGE_FILE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ]
            )->addColumn(
                MessageFileInterface::MESSAGE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )->addColumn(
                MessageFileInterface::FILEPATH,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false, 'default' => ''
                ]
            )->addColumn(
                MessageFileInterface::FILENAME,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false, 'default' => ''
                ]
            )->addColumn(
                MessageFileInterface::URL_HASH,
                Table::TYPE_TEXT,
                32,
                [
                    'nullable' => false, 'default' => ''
                ]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    MessageFileInterface::MESSAGE_ID,
                    $messageTable,
                    MessageInterface::MESSAGE_ID
                ),
                MessageFileInterface::MESSAGE_ID,
                $messageTable,
                MessageInterface::MESSAGE_ID,
                Table::ACTION_CASCADE
            );
    }
}
