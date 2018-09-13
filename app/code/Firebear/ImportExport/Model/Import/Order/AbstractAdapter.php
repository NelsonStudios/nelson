<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import\Order;

use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Firebear\ImportExport\Model\Import\ImportAdapterInterface;
use Firebear\ImportExport\Model\Import\Context;
use Firebear\ImportExport\Traits\General as GeneralTrait;

/**
 * Order Abstract Import Adapter
 */
abstract class AbstractAdapter extends AbstractEntity implements ImportAdapterInterface
{
    use GeneralTrait;

    /**
     * Keys Which Used To Build Result Data Array For Future Update
     */
    const ENTITIES_TO_CREATE_KEY = 'entities_to_create';

    const ENTITIES_TO_UPDATE_KEY = 'entities_to_update';

    /**
     * Id Of Next Entity Row
     *
     * @var int
     */
    protected $_nextEntityId;

    /**
     * Entities Information From Import File
     *
     * @var array
     */
    protected $_newEntities = [];

    /**
     * Order Ids Map
     *
     * @var array
     */
    protected $orderIdsMap;

    /**
     * Shipment Ids Map
     *
     * @var array
     */
    protected $shipmentIdsMap;

    /**
     * Payment Ids Map
     *
     * @var array
     */
    protected $paymentIdsMap;

    /**
     * Invoice Ids Map
     *
     * @var array
     */
    protected $invoiceIdsMap;

    /**
     * Creditmemo Ids Map
     *
     * @var array
     */
    protected $creditmemoIdsMap;

    /**
     * Item Ids Map
     *
     * @var array
     */
    protected $itemIdsMap;

    /**
     * Tax Ids Map
     *
     * @var array
     */
    protected $taxIdsMap;

    /**
     * Validation Failure Message Template Definitions
     *
     * @var array
     */
    protected $_messageTemplates = [];

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Field Processor
     *
     * @var \Firebear\ImportExport\Model\Import\Order\FieldProcessor
     */
    protected $_fieldProcessor;

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $_mainTable;

    /**
     * Store Table Name
     *
     * @var string
     */
    protected $_storeTable = 'store';

    /**
     * Order Entity Name
     *
     * @var string
     */
    protected $_orderTable = 'sales_order';

    /**
     * Customer Entity Table Name
     *
     * @var string
     */
    protected $_customerTable = 'customer_entity';

    /**
     * Order Address Table Name
     *
     * @var string
     */
    protected $_addressTable = 'sales_order_address';

    /**
     * Order Shipment Table Name
     *
     * @var string
     */
    protected $_shipmentTable = 'sales_shipment';

    /**
     * Product Entity Table Name
     *
     * @var string
     */
    protected $_productTable = 'catalog_product_entity';
    
    /**
     * Resource Connection
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Initialize Import
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->_logger = $context->getLogger();
        $this->_fieldProcessor = $context->getFieldProcessor();
        $this->_resource = $context->getResource();

        parent::__construct(
            $context->getJsonHelper(),
            $context->getImportExportData(),
            $context->getDataSourceModel(),
            $context->getConfig(),
            $context->getResource(),
            $context->getResourceHelper(),
            $context->getStringUtils(),
            $context->getErrorAggregator()
        );
        $this->initErrorTemplates();
    }

    /**
     * Retrieve Order Ids Map
     *
     * @return array
     */
    public function getOrderIdsMap()
    {
        return $this->orderIdsMap ?: [];
    }

    /**
     * Set Order Ids Map
     *
     * @param array $orderIds
     * @return $this
     */
    public function setOrderIdsMap(array $orderIds)
    {
        $this->orderIdsMap = $orderIds;

        return $this;
    }

    /**
     * Retrieve Shipment Ids Map
     *
     * @return array
     */
    public function getShipmentIdsMap()
    {
        return $this->shipmentIdsMap ?: [];
    }

    /**
     * Set Shipment Ids Map
     *
     * @param array $shipmentIds
     * @return $this
     */
    public function setShipmentIdsMap(array $shipmentIds)
    {
        $this->shipmentIdsMap = $shipmentIds;

        return $this;
    }

    /**
     * Retrieve Payment Ids Map
     *
     * @return array
     */
    public function getPaymentIdsMap()
    {
        return $this->paymentIdsMap ?: [];
    }

    /**
     * Set Payment Ids Map
     *
     * @param array $paymentIds
     * @return $this
     */
    public function setPaymentIdsMap(array $paymentIds)
    {
        $this->paymentIdsMap = $paymentIds;

        return $this;
    }

    /**
     * Retrieve Invoice Id Map
     *
     * @return array|null
     */
    public function getInvoiceIdsMap()
    {
        return $this->invoiceIdsMap ?: [];
    }

    /**
     * Set Invoice Ids Map
     *
     * @param array $invoiceIds
     * @return $this
     */
    public function setInvoiceIdsMap(array $invoiceIds)
    {
        $this->invoiceIdsMap = $invoiceIds;

        return $this;
    }

    /**
     * Retrieve Creditmemo Id Map
     *
     * @return array
     */
    public function getCreditmemoIdsMap()
    {
        return $this->creditmemoIdsMap ?: [];
    }

    /**
     * Set Creditmemo Ids Map
     *
     * @param array $creditmemoIds
     * @return $this
     */
    public function setCreditmemoIdsMap(array $creditmemoIds)
    {
        $this->creditmemoIdsMap = $creditmemoIds;

        return $this;
    }

    /**
     * Retrieve Item Ids Map
     *
     * @return array
     */
    public function getItemIdsMap()
    {
        return $this->itemIdsMap ?: [];
    }

    /**
     * Set Item Ids Map
     *
     * @param array $itemIds
     * @return $this
     */
    public function setItemIdsMap(array $itemIds)
    {
        $this->itemIdsMap = $itemIds;

        return $this;
    }

    /**
     * Retrieve Tax Ids Map
     *
     * @return array
     */
    public function getTaxIdsMap()
    {
        return $this->taxIdsMap ?: [];
    }

    /**
     * Set Tax Ids Map
     *
     * @param array $taxIds
     * @return $this
     */
    public function setTaxIdsMap(array $taxIds)
    {
        $this->taxIdsMap = $taxIds;

        return $this;
    }

    /**
     * Retrieve All Fields Source
     *
     * @return array
     */
    public function getAllFields()
    {
        return array_keys($this->_connection->describeTable(
            $this->_resource->getTableName($this->_mainTable)
        ));
    }

    /**
     * Import Behavior Getter
     *
     * @return string
     */
    public function getBehavior()
    {
        if (!isset($this->_parameters['behavior']) ||
            $this->_parameters['behavior'] != Import::BEHAVIOR_ADD_UPDATE &&
            $this->_parameters['behavior'] != Import::BEHAVIOR_REPLACE &&
            $this->_parameters['behavior'] != Import::BEHAVIOR_DELETE
        ) {
            return Import::getDefaultBehavior();
        }
        return $this->_parameters['behavior'];
    }

    /**
     * Import Data Rows
     *
     * @return boolean
     */
    protected function _importData()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $toCreate = [];
            $toUpdate = [];
            $toDelete = [];

            foreach ($bunch as $rowNumber => $rowData) {
                $rowData = $this->prepareRowData($rowData);
                /* validate data */
                if (!$rowData || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }
                /* behavior selector */
                switch ($this->getBehavior()) {
                    case Import::BEHAVIOR_DELETE:
                        $toDelete[] = $this->_getIdForDelete($rowData);
                        break;
                    case Import::BEHAVIOR_REPLACE:
                        $data = $this->_prepareDataForReplace($rowData);
                        $toUpdate = array_merge($toUpdate, $data[self::ENTITIES_TO_UPDATE_KEY]);
                        break;
                    case Import::BEHAVIOR_ADD_UPDATE:
                        $data = $this->_prepareDataForUpdate($rowData);
                        $toCreate = array_merge($toCreate, $data[self::ENTITIES_TO_CREATE_KEY]);
                        $toUpdate = array_merge($toUpdate, $data[self::ENTITIES_TO_UPDATE_KEY]);
                        break;
                }
            }
            /* save prepared data */
            if ($toCreate || $toUpdate) {
                $this->_saveEntities($toCreate, $toUpdate);
            }
            if ($toDelete) {
                $this->_deleteEntities($toDelete);
            }
        }
        return true;
    }

    /**
     * Retrieve The Prepared Data
     *
     * @param array $rowData
     * @return array|bool
     */
    public function prepareRowData(array $rowData)
    {
        return $rowData;
    }

    /**
     * Is Empty Row
     *
     * @param array $rowData
     * @return bool
     */
    public function isEmptyRow($rowData)
    {
        if ($this->getBehavior() == Import::BEHAVIOR_DELETE) {
            return false;
        }
        /* check empty field */
        $empty = true;
        foreach ($this->getAllFields() as $field) {
            if (!empty($rowData[$field]) && $field != static::COLUMN_ENTITY_ID) {
                $empty = false;
                break;
            }
        }
        return $empty;
    }

    /**
     * Retrieve The Prepared DateTime Values
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareDateTime(array $rowData)
    {
        $now = new \DateTime();
        $createdAt = empty($rowData['created_at'])
            ? $now
            : (new \DateTime())->setTimestamp(strtotime($rowData['created_at']));

        return [
            $createdAt->format(DateTime::DATETIME_PHP_FORMAT),
            $now->format(DateTime::DATETIME_PHP_FORMAT)
        ];
    }

    /**
     * Retrieve Exploded Field
     *
     * @param string $field
     * @return array|bool
     */
    protected function _explodeField($field)
    {
        return $this->_fieldProcessor->explode($field, $this->_getSeparator());
    }

    /**
     * Retrieve Import Multiple Value Separator
     *
     * @return string
     */
    protected function _getSeparator()
    {
        return $this->_parameters['_import_multiple_value_separator'];
    }

    /**
     * Retrieve Entity Id If Entity Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _getEntityId(array $rowData)
    {
        return $rowData[static::COLUMN_ENTITY_ID];
    }

    /**
     * Retrieve Order Id If Order Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _getOrderId(array $rowData)
    {
        if (null !== $this->orderIdsMap) {
            $orderId = $rowData[static::COLUMN_ORDER_ID];
            if (isset($this->orderIdsMap[$orderId])) {
                return $this->orderIdsMap[$orderId];
            }
        }
        return false;
    }

    /**
     * Retrieve Order Item Id If Order Item Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _getOrderItemId(array $rowData)
    {
        if (null !== $this->itemIdsMap) {
            $itemId = $rowData[static::COLUMN_ORDER_ITEM_ID];
            if (isset($this->itemIdsMap[$itemId])) {
                return $this->itemIdsMap[$itemId];
            }
        }
        return false;
    }

    /**
     * Retrieve Shipment Id If Shipment Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _getShipmentId(array $rowData)
    {
        if (null !== $this->shipmentIdsMap) {
            $shipmentId = $rowData[static::COLUMN_SHIPMENT_ID];
            if (isset($this->shipmentIdsMap[$shipmentId])) {
                return $this->shipmentIdsMap[$shipmentId];
            }
        }
        return false;
    }

    /**
     * Retrieve Payment Id If Payment Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _getPaymentId(array $rowData)
    {
        if (null !== $this->paymentIdsMap) {
            $paymentId = $rowData[static::COLUMN_PAYMENT_ID];
            if (isset($this->paymentIdsMap[$paymentId])) {
                return $this->paymentIdsMap[$paymentId];
            }
        }
        return false;
    }

    /**
     * Retrieve Invoice Id If Invoice Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _getInvoiceId(array $rowData)
    {
        if (null !== $this->invoiceIdsMap) {
            $invoiceId = $rowData[static::COLUMN_INVOICE_ID];
            if (isset($this->invoiceIdsMap[$invoiceId])) {
                return $this->invoiceIdsMap[$invoiceId];
            }
        }
        return false;
    }

    /**
     * Retrieve Creditmemo Id If Creditmemo Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _getCreditmemoId(array $rowData)
    {
        if (null !== $this->creditmemoIdsMap) {
            $creditmemoId = $rowData[static::COLUMN_CREDITMEMO_ID];
            if (isset($this->creditmemoIdsMap[$creditmemoId])) {
                return $this->creditmemoIdsMap[$creditmemoId];
            }
        }
        return false;
    }

    /**
     * Retrieve Tax Id If Tax Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _getTaxId(array $rowData)
    {
        if (null !== $this->taxIdsMap) {
            $taxId = $rowData[static::COLUMN_TAX_ID];
            if (isset($this->taxIdsMap[$taxId])) {
                return $this->taxIdsMap[$taxId];
            }
        }
        return false;
    }

    /**
     * Prepare Data For Update
     *
     * @param array $rowData
     * @return array
     */
    abstract protected function _prepareDataForUpdate(array $rowData);

    /**
     * Prepare Data For Replace
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareDataForReplace(array $rowData)
    {
        $toUpdate = [];
        $entityRow = [
            static::COLUMN_ENTITY_ID => $this->_getEntityId($rowData)
        ];
        /* prepare data */
        $toUpdate[] = $this->_prepareEntityRow($entityRow, $rowData);
        return [
            self::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }

    /**
     * Retrieve Id For Delete
     *
     * @param array $rowData
     * @return string
     */
    protected function _getIdForDelete(array $rowData)
    {
        return $this->_getEntityId($rowData);
    }

    /**
     * Update And Insert Data In Entity Table
     *
     * @param array $toCreate Rows for insert
     * @param array $toUpdate Rows for update
     * @return $this
     */
    protected function _saveEntities(array $toCreate, array $toUpdate)
    {
        if ($toCreate) {
            $this->_connection->insertMultiple(
                $this->getMainTable(),
                $toCreate
            );
        }
        if ($toUpdate) {
            $this->_connection->insertOnDuplicate(
                $this->getMainTable(),
                $toUpdate,
                $this->_getEntityFieldsToUpdate($toUpdate)
            );
        }
        return $this;
    }

    /**
     * Filter The Entity That Are Being Updated So We Only Change Fields Found In The Importer File
     *
     * @param array $toUpdate
     * @return array
     */
    protected function _getEntityFieldsToUpdate(array $toUpdate)
    {
        $firstEntity = reset($toUpdate);
        $columnsToUpdate = array_keys($firstEntity);
        $fieldsToUpdate = array_filter(
            $this->getAllFields(),
            function ($field) use ($columnsToUpdate) {
                return in_array($field, $columnsToUpdate);
            }
        );
        return $fieldsToUpdate;
    }

    /**
     * Prepare Entity Field Values
     *
     * @param array $toUpdate
     * @param array $toUpdate
     * @return array
     */
    protected function _prepareEntityRow(array $entityRow, array $rowData)
    {
        $keys = array_keys($entityRow);
        foreach ($this->getAllFields() as $field) {
            if (!in_array($field, $keys) && isset($rowData[$field])) {
                $entityRow[$field] = $rowData[$field];
            }
        }
        return $entityRow;
    }

    /**
     * Delete List Of Entities
     *
     * @param array $toDelete Entities Id List
     * @return $this
     */
    protected function _deleteEntities(array $toDelete)
    {
        $condition = $this->_connection->quoteInto(
            static::COLUMN_ENTITY_ID . ' IN (?)',
            $toDelete
        );
        $this->_connection->delete($this->getMainTable(), $condition);

        return $this;
    }

    /**
     * Validate Data Row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        if (isset($this->_validatedRows[$rowNumber])) {
            // check that row is already validated
            return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
        }
        $this->_validatedRows[$rowNumber] = true;
        $this->_processedEntitiesCount++;
        /* behavior selector */
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->_validateRowForDelete($rowData, $rowNumber);
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->_validateRowForReplace($rowData, $rowNumber);
                break;
            case Import::BEHAVIOR_ADD_UPDATE:
                $this->_validateRowForUpdate($rowData, $rowNumber);
                break;
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }


    /**
     * Validate Row Data For Replace Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function _validateRowForReplace(array $rowData, $rowNumber)
    {
        if ($this->_checkEntityIdKey($rowData, $rowNumber)) {
            $entityId = $rowData[static::COLUMN_ENTITY_ID];
            if (isset($this->_newEntities[$entityId])) {
                $this->addRowError(static::ERROR_DUPLICATE_ENTITY_ID, $rowNumber);
            }
        }
    }

    /**
     * Validate Row Data For Add/Update Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    abstract protected function _validateRowForUpdate(array $rowData, $rowNumber);


    /**
     * Validate Row Data For Delete Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        $this->_checkEntityIdKey($rowData, $rowNumber);
    }

    /**
     * General Check Of Unique Key
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _checkEntityIdKey(array $rowData, $rowNumber)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID])) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_EMPTY, $rowNumber);
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    /**
     * Check Of Increment Id Key
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _checkIncrementIdKey(array $rowData, $rowNumber)
    {
        if (empty($rowData[static::COLUMN_INCREMENT_ID])) {
            $this->addRowError(static::ERROR_INCREMENT_ID_IS_EMPTY, $rowNumber);
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    /**
     * Check Disjunction Key
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _checkDisjunctionKey(array $rowData, $rowNumber)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID]) &&
            empty($rowData[static::COLUMN_INCREMENT_ID])
        ) {
            $this->addRowError(static::ERROR_INCREMENT_ID_IS_EMPTY, $rowNumber);
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    /**
     * Save Validated Bunches
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->_resourceHelper->getMaxDataSize();
        $bunchSize = $this->_importExportData->getBunchSize();

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();
        $file = null;
        $jobId = null;
        if (isset($this->_parameters['file'])) {
            $file = $this->_parameters['file'];
        }
        if (isset($this->_parameters['job_id'])) {
            $jobId = $this->_parameters['job_id'];
        }

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->_dataSourceModel->saveBunches(
                    $this->getEntityTypeCode(),
                    $this->getBehavior(),
                    $jobId,
                    $file,
                    $bunchRows
                );
                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen(\Zend\Serializer\Serializer::serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }

            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                $this->_processedRowsCount++;
                $rowData = $this->customBunchesData($rowData);
                $rowSize = strlen($this->jsonHelper->jsonEncode($rowData));

                $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;

                if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                    $startNewBunch = true;
                    $nextRowBackup = [$source->key() => $rowData];
                } else {
                    $bunchRows[$source->key()] = $rowData;
                    $currentDataSize += $rowSize;
                }

                $source->next();
            }
        }
        return $this;
    }

    /**
     * Initialize Error Templates
     *
     * @return void
     */
    public function initErrorTemplates()
    {
        foreach ($this->_messageTemplates as $errorCode => $template) {
            $this->addMessageTemplate($errorCode, $template);
        }
    }

    /**
     * Retrieve Entity Type Code
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_TYPE_CODE;
    }

    /**
     * Retrieve Next Entity Id
     *
     * @return int
     */
    protected function _getNextEntityId()
    {
        if (!$this->_nextEntityId) {
            $this->_nextEntityId = $this->_resourceHelper->getNextAutoincrement(
                $this->getMainTable()
            );
        }
        return $this->_nextEntityId++;
    }

    /**
     * Retrieve Customer Id
     *
     * @param string $email
     * @param int $storeId
     * @return bool|int
     */
    public function getCustomerId($email, $storeId)
    {
        $bind = [':email' => $email, ':store_id' => $storeId];
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->_connection->select();
        $select->from(['e' => $this->getCustomerTable()], 'e.entity_id')
            ->join(
                ['s' => $this->getStoreTable()],
                'e.website_id=s.website_id',
                []
            )
            ->where('e.email = :email')
            ->where('s.store_id = :store_id');

        $result = $this->_connection->fetchOne($select, $bind);
        return $result ? $result : null;
    }

    /**
     * Retrieve Customer Group Id
     *
     * @param int $customerId
     * @return bool|int
     */
    public function getCustomerGroupId($customerId)
    {
        $bind = [':entity_id' => $customerId];
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->_connection->select();
        $select->from($this->getCustomerTable(), 'group_id')
            ->where('entity_id = :entity_id');

        return $this->_connection->fetchOne($select, $bind);
    }

    /**
     * Retrieve Product Id By Sku
     *
     * @param string $sku
     * @return bool|int
     */
    public function getProductIdBySku($sku)
    {
        $bind = [':sku' => (string)$sku];
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->_connection->select();
        $select->from($this->getProductTable(), 'entity_id')
            ->where('sku = :sku');

        return $this->_connection->fetchOne($select, $bind);
    }

    /**
     * Retrieve Customer Entity Table Name
     *
     * @return string
     */
    public function getStoreTable()
    {
        return $this->_resource->getTableName(
            $this->_storeTable
        );
    }

    /**
     * Retrieve Order Entity Table Name
     *
     * @return string
     */
    public function getOrderTable()
    {
        return $this->_resource->getTableName(
            $this->_orderTable
        );
    }

    /**
     * Retrieve Customer Entity Table Name
     *
     * @return string
     */
    public function getCustomerTable()
    {
        return $this->_resource->getTableName(
            $this->_customerTable
        );
    }

    /**
     * Retrieve Order Address Table Name
     *
     * @return string
     */
    public function getAddressTable()
    {
        return $this->_resource->getTableName(
            $this->_addressTable
        );
    }

    /**
     * Retrieve Order Shipment Table Name
     *
     * @return string
     */
    public function getShipmentTable()
    {
        return $this->_resource->getTableName(
            $this->_shipmentTable
        );
    }

    /**
     * Retrieve Main Table Name
     *
     * @return string
     */
    public function getMainTable()
    {
        return $this->_resource->getTableName(
            $this->_mainTable

        );
    }

    /**
     * Retrieve Product Entity Table Name
     *
     * @return string
     */
    public function getProductTable()
    {
        return $this->_resource->getTableName(
            $this->_productTable
        );
    }
}
