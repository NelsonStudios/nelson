<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import;

use Magento\CustomerImportExport\Model\Import\CustomerComposite as MagentoCustomer;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Magento\Framework\App\ObjectManager;
use \Magento\ImportExport\Model\Import\AbstractEntity;

class CustomerComposite extends MagentoCustomer
{
    use \Firebear\ImportExport\Traits\General;

    protected $specialFields = [
        'reward_update_notification',
        'reward_warning_notification'
    ];
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var ConsoleOutput
     */
    protected $output;

    protected $_debugMode;

    protected $duplicateFields = [\Magento\CustomerImportExport\Model\Import\Customer::COLUMN_EMAIL];

    protected $_customerEntity;

    /**
     *
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory $dataFactory
     * @param \Magento\CustomerImportExport\Model\Import\CustomerFactory $customerFactory
     * @param \Magento\CustomerImportExport\Model\Import\AddressFactory $addressFactory
     * @param \Symfony\Component\Console\Output\ConsoleOutput $output
     * @param \Firebear\ImportExport\Helper\Data $helper
     * @param \Firebear\ImportExport\Model\Import\CustomerFactory $fireImportCustomer
     * @param LoggerInterface $logger
     * @param \Firebear\ImportExport\Model\ResourceModel\Import\Data $importFireData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\CustomerImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory $dataFactory,
        \Magento\CustomerImportExport\Model\Import\CustomerFactory $customerFactory,
        \Magento\CustomerImportExport\Model\Import\AddressFactory $addressFactory,
        \Symfony\Component\Console\Output\ConsoleOutput $output,
        \Firebear\ImportExport\Helper\Data $helper,
        \Firebear\ImportExport\Model\Import\CustomerFactory $fireImportCustomer,
        \Firebear\ImportExport\Model\Import\AddressFactory $fireImportAddress,
        LoggerInterface $logger,
        \Firebear\ImportExport\Model\ResourceModel\Import\CustomerComposite\DataFactory $importFireData,
        array $data = []
    )
    {
        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $dataFactory,
            $customerFactory,
            $addressFactory,
            $data
        );
        $this->output = $output;
        $this->_logger = $logger;
        $this->_debugMode = $helper->getDebugMode();
        $this->_customerEntity = $fireImportCustomer->create(['data' => $data]);

        // address entity stuff
        $data['data_source_model'] = $importFireData->create(
            [
                'arguments' => [
                    'entity_type' => 'address',
                    'customer_attributes' => $this->_customerAttributes,
                ],
            ]
        );
        $this->_addressEntity = $fireImportAddress->create(['data' => $data]);
        $this->fireImportAddress = $fireImportAddress->create();
        $this->fireImportCustomer = $fireImportCustomer->create();
        unset($data['data_source_model']);
        $this->_dataSourceModel = $importFireData->create();
    }

    /**
     * @return array
     */
    public function getAllFields()
    {
        $options = array_merge($this->getValidColumnNames(), $this->_specialAttributes);
        $options = array_merge($options, $this->_permanentAttributes);
        $options = array_merge($options, $this->specialFields);

        return array_unique($options);
    }

    public function setLogger($logger)
    {
        $this->_logger = $logger;
        $this->_customerEntity->setLogger($logger);
    }

    protected function _importData()
    {
        $oldEmail = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entitiesToCreate = [];
            $entitiesToUpdate = [];
            $entitiesToDelete = [];
            $attributesToSave = [];
            $newRows = [];
            $updateRows = [];
            $attributes = [];
            $defaults = [];
            $deleteRowIds = [];

            foreach ($bunch as $rowNumber => $rowData) {
                $time = explode(" ", microtime());
                $startTime = $time[0] + $time[1];
                $email = $rowData['email'];
                $rowData = $this->joinIdenticalyData($rowData);

                if (in_array($email, $oldEmail)) {
                    unset($rowData[$email]);
                    continue;
                } else {
                    array_push($oldEmail, $email);
                }
                $website = $rowData[Customer::COLUMN_WEBSITE];
                if (isset($this->_newCustomers[strtolower($rowData[Customer::COLUMN_EMAIL])][$website])) {
                    continue;
                }
                $rowData = $this->customChangeData($rowData);
                if (!$this->validateRow($rowData, $rowNumber)) {
                    $this->addLogWriteln(__('customer with email: %1 is not valided', $email), $this->output, 'info');
                    continue;
                }
                if ($this->fireImportAddress->_isOptionalAddressEmpty($rowData) || !$this->validateRow($rowData, $rowNumber)) {
                    $this->addLogWriteln(__('address with email: %1 is not valided', $email), $this->output, 'info');
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                if ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE) {
                    $entitiesToDelete[] = $this->_getCustomerId(
                        $rowData[Customer::COLUMN_EMAIL],
                        $rowData[Customer::COLUMN_WEBSITE]
                    );
                    $deleteRowIds[] = $rowData[Address::COLUMN_ADDRESS_ID];
                } elseif ($this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE
                    || $this->getBehavior($rowData) == \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND) {
                    $processedData = $this->fireImportCustomer->_prepareDataForUpdate($rowData);
                    
                    $entitiesToCreate = array_merge($entitiesToCreate, $processedData[Customer::ENTITIES_TO_CREATE_KEY]);
                    $lastEntity = $processedData[Customer::ENTITIES_TO_CREATE_KEY][0] ?? null;
                    if ($lastEntity) {
						$rowData['parent_id'] = $lastEntity['entity_id'];
						$this->fireImportAddress->setCustomerId(
							$lastEntity['email'],
							$lastEntity['website_id'],
							$lastEntity['entity_id']
						);
                    } 
                    
                    $entitiesToUpdate = array_merge($entitiesToUpdate, $processedData[Customer::ENTITIES_TO_UPDATE_KEY]);
                    foreach ($processedData[Customer::ATTRIBUTES_TO_SAVE_KEY] as $tableName => $customerAttributes) {
                        if (!isset($attributesToSave[$tableName])) {
                            $attributesToSave[$tableName] = [];
                        }
                        $attributesToSave[$tableName] =
                            array_diff_key($attributesToSave[$tableName], $customerAttributes)
                            + $customerAttributes;
                    }
                    $addUpdateResult = $this->fireImportAddress->_prepareDataForUpdate($rowData);
                    if ($addUpdateResult['entity_row_new']) {
                        $newRows[] = $addUpdateResult['entity_row_new'];
                    }
                    if ($addUpdateResult['entity_row_update']) {
                        $updateRows[] = $addUpdateResult['entity_row_update'];
                    }
                    $attributes = $this->fireImportAddress->_mergeEntityAttributes($addUpdateResult['attributes'], $attributes);
                    $defaults = $this->fireImportAddress->_mergeEntityAttributes($addUpdateResult['defaults'], $defaults);
                }
                $time = explode(" ", microtime());
                $endTime = $time[0] + $time[1];
                $totalTime = $endTime - $startTime;
                $totalTime = round($totalTime, 5);

                $this->addLogWriteln(__('customer_composite with email: %1 .... %2s', $email, $totalTime), $this->output, 'info');
                $this->addLogWriteln(__('address_composite with email: %1 .... %2s', $email, $totalTime), $this->output, 'info');
            }
            try {
                $this->updateItemsCounterStats($entitiesToCreate, $entitiesToUpdate, $entitiesToDelete);

                /**
                 * Save prepared data
                 */

                if ($entitiesToCreate || $entitiesToUpdate) {
                    $this->fireImportCustomer->_saveCustomerEntities($entitiesToCreate, $entitiesToUpdate);
                }
                if ($attributesToSave) {
                    $this->fireImportCustomer->_saveCustomerAttributes($attributesToSave);
                }
                if ($entitiesToDelete) {
                    $this->fireImportCustomer->_deleteCustomerEntities($entitiesToDelete);
                }
                $this->updateItemsCounterStats($newRows, $updateRows, $deleteRowIds);
                if ($newRows || $updateRows) {
                    $this->fireImportAddress->_saveAddressEntities(
                        $newRows,
                        $updateRows
                    );
                }
                if ($attributes) {
                    $this->fireImportAddress->_saveAddressAttributes(
                        $attributes
                    );
                }
                if ($defaults) {
                    $this->fireImportAddress->_saveCustomerDefaults(
                        $defaults
                    );
                }

                if ($deleteRowIds) {
                    $this->fireImportAddress->_deleteAddressEntities($deleteRowIds);
                }
            } catch (\Exception $e) {
                $this->addLogWriteln($e->getMessage(), $this->output, 'error');
            }
        }

        return true;
    }


    protected function _saveValidatedBunches()
    {
        $source = $this->getSource();
        $bunchRows = [];
        $startNewBunch = false;

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();
        $masterAttributeCode = $this->getMasterAttributeCode();
        $file = null;
        $jobId = null;
        if (isset($this->_parameters['file'])) {
            $file = $this->_parameters['file'];
        }
        if (isset($this->_parameters['job_id'])) {
            $jobId = $this->_parameters['job_id'];
        }
        $prevData = [];
        while ($source->valid() || count($bunchRows) || isset($entityGroup)) {
            if ($startNewBunch || !$source->valid()) {
                /* If the end approached add last validated entity group to the bunch */
                if (!$source->valid() && isset($entityGroup)) {
                    foreach ($entityGroup as $key => $value) {
                        $bunchRows[$key] = $value;
                    }
                    unset($entityGroup);
                }
                $this->_dataSourceModel->saveBunches(
                    $this->getEntityTypeCode(),
                    $this->getBehavior(),
                    $jobId,
                    $file,
                    $bunchRows
                );

                $bunchRows = [];
                $startNewBunch = false;
            }
            if ($source->valid()) {
                $valid = true;
                try {
                    $rowData = $source->current();
                    foreach ($rowData as $attrName => $element) {
                        if (!mb_check_encoding($element, 'UTF-8')) {
                            $valid = false;
                            $this->addRowError(
                                AbstractEntity::ERROR_CODE_ILLEGAL_CHARACTERS,
                                $this->_processedRowsCount,
                                $attrName
                            );
                        }
                    }
                } catch (\InvalidArgumentException $e) {
                    $valid = false;
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                }

                if (!empty($prevData) && (!isset($rowData['email']) || empty($rowData['email']))) {
                    $rowData = array_merge($prevData, $this->deleteEmpty($rowData));
                }

                $prevData = $rowData;

                if (!$valid) {
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                if (isset($rowData[$masterAttributeCode]) && trim($rowData[$masterAttributeCode])) {
                    /* Add entity group that passed validation to bunch */
                    if (isset($entityGroup)) {
                        foreach ($entityGroup as $key => $value) {
                            $bunchRows[$key] = $value;
                        }
                        $productDataSize = strlen(\Zend\Serializer\Serializer::serialize($bunchRows));

                        /* Check if the new bunch should be started */
                        $isBunchSizeExceeded = ($this->_bunchSize > 0 && count($bunchRows) >= $this->_bunchSize);
                        $startNewBunch = $productDataSize >= $this->_maxDataSize || $isBunchSizeExceeded;
                    }

                    /* And start a new one */
                    $entityGroup = [];
                }

                if (isset($entityGroup) && $this->validateRow($rowData, $source->key())) {
                    /* Add row to entity group */
                    $entityGroup[$source->key()] = $this->_prepareRowForDb($rowData);
                } elseif (isset($entityGroup)) {
                    /* In case validation of one line of the group fails kill the entire group */
                    unset($entityGroup);
                }
                //   $platformModel = $this->helper->getPlatformModel($this->_parameters['platforms']);

                // $rowData = $platformModel->prepareRow($rowData);
                $this->_processedRowsCount++;
                $source->next();
            }
        }
        return $this;
    }

    protected function deleteEmpty($array)
    {
        if (isset($array['sku'])) {
            unset($array['sku']);
        }
        $newElement = [];
        foreach ($array as $key => $element) {
            if (strlen($element)) {
                $newElement[$key] = $element;
            }
        }

        return $newElement;
    }
}
