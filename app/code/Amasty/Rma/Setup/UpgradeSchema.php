<?php

namespace Amasty\Rma\Setup;

use Amasty\Rma\Utils\FileUpload;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\CreateConditionTable
     */
    private $createConditionTable;

    /**
     * @var Operation\CreateReasonTable
     */
    private $createReasonTable;

    /**
     * @var Operation\CreateResolutionTable
     */
    private $createResolutionTable;

    /**
     * @var Operation\CreateConditionStoreTable
     */
    private $createConditionStoreTable;

    /**
     * @var Operation\CreateReasonStoreTable
     */
    private $createReasonStoreTable;

    /**
     * @var Operation\CreateResolutionStoreTable
     */
    private $createResolutionStoreTable;

    /**
     * @var Operation\CreateStatusTable
     */
    private $createStatusTable;

    /**
     * @var Operation\CreateStatusStoreTable
     */
    private $createStatusStoreTable;

    /**
     * @var Operation\CreateMessageTable
     */
    private $createMessageTable;

    /**
     * @var Operation\CreateMessageFileTable
     */
    private $createMessageFileTable;

    /**
     * @var Operation\CreateRequestTable
     */
    private $createRequestTable;

    /**
     * @var Operation\CreateRequestItemTable
     */
    private $createRequestItemTable;

    /**
     * @var Operation\CreateTrackingTable
     */
    private $createTrackingTable;

    /**
     * @var Operation\CreateGuestCreateRequestTable
     */
    private $createGuestCreateRequestTable;

    /**
     * @var Operation\CreateReturnRulesTable
     */
    private $createReturnRulesTable;

    /**
     * @var Operation\CreateReturnRulesResolutionsTable
     */
    private $createReturnRulesResolutionsTable;

    /**
     * @var Operation\CreateReturnRulesWebsitesTable
     */
    private $createReturnRulesWebsitesTable;

    /**
     * @var Operation\CreateReturnRulesCustomerGroupsTable
     */
    private $createReturnRulesCustomerGroupsTable;

    /**
     * @var Operation\CreateHistoryTable
     */
    private $createHistoryTable;

    public function __construct(
        Operation\CreateConditionTable $createConditionTable,
        Operation\CreateReasonTable $createReasonTable,
        Operation\CreateResolutionTable $createResolutionTable,
        Operation\CreateConditionStoreTable $createConditionStoreTable,
        Operation\CreateReasonStoreTable $createReasonStoreTable,
        Operation\CreateResolutionStoreTable $createResolutionStoreTable,
        Operation\CreateStatusTable $createStatusTable,
        Operation\CreateStatusStoreTable $createStatusStoreTable,
        Operation\CreateRequestTable $createRequestTable,
        Operation\CreateRequestItemTable $createRequestItemTable,
        Operation\CreateTrackingTable $createTrackingTable,
        Operation\CreateMessageTable $createMessageTable,
        Operation\CreateMessageFileTable $createMessageFileTable,
        Operation\CreateGuestCreateRequestTable $createGuestCreateRequestTable,
        Operation\CreateReturnRulesTable $createReturnRulesTable,
        Operation\CreateReturnRulesWebsitesTable $createReturnRulesWebsitesTable,
        Operation\CreateReturnRulesCustomerGroupsTable $createReturnRulesCustomerGroupsTable,
        Operation\CreateReturnRulesResolutionsTable $createReturnRulesResolutionsTable,
        Operation\CreateHistoryTable $createHistoryTable
    ) {
        $this->createConditionTable = $createConditionTable;
        $this->createReasonTable = $createReasonTable;
        $this->createResolutionTable = $createResolutionTable;
        $this->createConditionStoreTable = $createConditionStoreTable;
        $this->createReasonStoreTable = $createReasonStoreTable;
        $this->createResolutionStoreTable = $createResolutionStoreTable;
        $this->createStatusTable = $createStatusTable;
        $this->createStatusStoreTable = $createStatusStoreTable;
        $this->createMessageTable = $createMessageTable;
        $this->createMessageFileTable = $createMessageFileTable;
        $this->createRequestTable = $createRequestTable;
        $this->createRequestItemTable = $createRequestItemTable;
        $this->createTrackingTable = $createTrackingTable;
        $this->createGuestCreateRequestTable = $createGuestCreateRequestTable;
        $this->createReturnRulesTable = $createReturnRulesTable;
        $this->createReturnRulesWebsitesTable = $createReturnRulesWebsitesTable;
        $this->createReturnRulesCustomerGroupsTable = $createReturnRulesCustomerGroupsTable;
        $this->createReturnRulesResolutionsTable = $createReturnRulesResolutionsTable;
        $this->createHistoryTable = $createHistoryTable;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.0.0', '<')) {
            if ($context->getVersion()) {
                $disabled = explode(',', str_replace(' ', ',', ini_get('disable_functions')));
                if (!in_array('class_exists', $disabled)
                    && function_exists('class_exists')
                    && class_exists(\Amasty\Rma\Helper\Data::class)) {
                    throw new \RuntimeException("This update requires removing folder app/code/Amasty/Rma.\n"
                        . "Remove this folder and unpack new version of package into app/code/Amasty/Rma.\n"
                        . "Run `php bin/magento setup:upgrade` again\n");
                }
            }
            $this->createConditionTable->execute($setup);
            $this->createReasonTable->execute($setup);
            $this->createResolutionTable->execute($setup);
            $this->createStatusTable->execute($setup);
            $this->createRequestTable->execute($setup);
            $this->createRequestItemTable->execute($setup);
            $this->createTrackingTable->execute($setup);
            $this->createMessageTable->execute($setup);
            $this->createConditionStoreTable->execute($setup);
            $this->createReasonStoreTable->execute($setup);
            $this->createResolutionStoreTable->execute($setup);
            $this->createStatusStoreTable->execute($setup);
            $this->createMessageFileTable->execute($setup);
            $this->createGuestCreateRequestTable->execute($setup);
            $this->createReturnRulesTable->execute($setup);
            $this->createReturnRulesWebsitesTable->execute($setup);
            $this->createReturnRulesCustomerGroupsTable->execute($setup);
            $this->createReturnRulesResolutionsTable->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->createHistoryTable->execute($setup);
        }

        $setup->endSetup();
    }
}
