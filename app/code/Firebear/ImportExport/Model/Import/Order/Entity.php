<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExport\Model\Import\Order;

use Magento\ImportExport\Model\Import;

/**
 * Order Entity Import
 */
class Entity extends AbstractAdapter
{
    /**
     * Entity Type Code
     *
     */
    const ENTITY_TYPE_CODE = 'order';
    
    /**
     * Entity Id Column Name
     *
     */
    const COLUMN_ENTITY_ID = 'entity_id'; 
    
    /**
     * Increment Id Column Name
     *
     */
    const COLUMN_INCREMENT_ID = 'increment_id'; 
	
    /**
     * Customer Id Column Name
     *
     */
    const COLUMN_CUSTOMER_ID = 'customer_id'; 
    
	
    /**
     * Customer Email Column Name
     *
     */
    const COLUMN_CUSTOMER_EMAIL = 'customer_email';     

    /**
     * Store Id Column Name
     *
     */
    const COLUMN_STORE_ID = 'store_id';
    
    /**
     * Error Codes
     */    
    const ERROR_DUPLICATE_INCREMENT_ID = 'duplicateOrderIncrementId'; 
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateOrderEntityId';
	const ERROR_INCREMENT_ID_IS_EMPTY = 'orderIncrementIdIsEmpty';
	const ERROR_ENTITY_ID_IS_EMPTY = 'orderEntityIdIsEmpty';
	const ERROR_STORE_ID_IS_EMPTY = 'orderStoreIdIsEmpty';
	
    /**
     * Validation Failure Message Template Definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Order entity_id is found more than once in the import file',
        self::ERROR_DUPLICATE_INCREMENT_ID => 'Order increment_id is found more than once in the import file',
        self::ERROR_INCREMENT_ID_IS_EMPTY => 'Order increment_id is empty',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Order entity_id is empty',
		self::ERROR_STORE_ID_IS_EMPTY => 'Order store_id is empty',
    ];
    
    /**
     * Order Entity Table Name
     *
     * @var string
     */
    protected $_mainTable = 'sales_order';
    
    /**
     * Order Status Table Name
     *
     * @var string
     */
    protected $_statusTable = 'sales_order_status';  
    
    /**
     * Order Status Collection
     *
     * @var array
     */
    protected $_status;
    
    /**
     * Retrieve The Prepared Data
     *
     * @param array $rowData
     * @return array|bool
     */
    public function prepareRowData(array $rowData)
    {
		$this->prepareStatus($rowData);
		return (!$this->isEmptyRow($rowData)) 
			? $rowData 
			: false;		
    }
    
    /**
     * Retrieve Entity Id If Entity Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _getExistEntityId(array $rowData)
    {
        $bind = [':increment_id' => $rowData[self::COLUMN_INCREMENT_ID]];
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->_connection->select();
        $select->from($this->getMainTable(), 'entity_id')
			->where('increment_id = :increment_id');
        
        return $this->_connection->fetchOne($select, $bind);
    } 
    
    /**
     * Prepare Data For Update
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareDataForUpdate(array $rowData)
    {
        $toCreate = [];
        $toUpdate = [];

        list($createdAt, $updatedAt) = $this->_prepareDateTime($rowData);

        $newEntity = false;
        $entityId = $this->_getExistEntityId($rowData);
        if (!$entityId) {
            /* create new entity id */
            $newEntity = true;
            $entityId = $this->_getNextEntityId();
            $this->_newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;
        }
        
		$this->orderIdsMap[$this->_getEntityId($rowData)] = $entityId;
		
		$customerId = null;
		$customerGroupId = 0;
		if (isset($rowData[self::COLUMN_CUSTOMER_EMAIL])) {
			$customerId = $this->getCustomerId(
				$rowData[self::COLUMN_CUSTOMER_EMAIL],
				$rowData[self::COLUMN_STORE_ID]
			);
			if ($customerId) {
				$customerGroupId = $this->getCustomerGroupId($customerId);
			}
		}

        $entityRow = [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'is_virtual' => empty($rowData['is_virtual']) ? 0 : 1,
			'customer_id' => $customerId,
			'customer_group_id' => $customerGroupId,
			'customer_is_guest' => $customerId ? 0 : 1,
            'entity_id' => $entityId
        ];        
		/* prepare data */
		$entityRow = $this->_prepareEntityRow($entityRow, $rowData);
        if ($newEntity) {
            $toCreate[] = $entityRow;
        } else {
            $toUpdate[] = $entityRow;
        }
        return [
            self::ENTITIES_TO_CREATE_KEY => $toCreate,
            self::ENTITIES_TO_UPDATE_KEY => $toUpdate
        ];
    }
	
    /**
     * Prepare Data For Replace
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareDataForReplace(array $rowData)
    {
        $toUpdate = [];
		$entityId = empty($rowData[self::COLUMN_INCREMENT_ID])
			? $this->_getEntityId($rowData)
			: $this->_getExistEntityId($rowData);
			
		$this->orderIdsMap[$entityId] = $entityId;		
		$entityRow = [
            self::COLUMN_ENTITY_ID => $entityId
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
        if (!empty($rowData[self::COLUMN_INCREMENT_ID])) {
			return $this->_getExistEntityId($rowData);
        }
        return parent::_getIdForDelete($rowData);
    }
    
    /**
     * Validate Row Data For Add/Update Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function _validateRowForUpdate(array $rowData, $rowNumber)
    {
        if ($this->_checkIncrementIdKey($rowData, $rowNumber)) {
            $incrementId = $rowData[self::COLUMN_INCREMENT_ID];
            if (isset($this->_newEntities[$incrementId])) {
                $this->addRowError(self::ERROR_DUPLICATE_INCREMENT_ID, $rowNumber);
            }
			
			if (empty($rowData[self::COLUMN_STORE_ID])) {
				$this->addRowError(self::ERROR_STORE_ID_IS_EMPTY, $rowNumber);
			}
		}
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
        $this->_checkDisjunctionKey($rowData, $rowNumber);
    }
    
    /**
     * Validate Row Data For Delete Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function _validateRowForDelete(array $rowData, $rowNumber)
    {
        $this->_checkDisjunctionKey($rowData, $rowNumber);
    } 
    
    /**
     * Delete List Of Entities
     *
     * @param array $toDelete Entities Id List
     * @return $this
     */
    protected function _deleteEntities(array $toDelete)
    {
        parent::_deleteEntities($toDelete);
        foreach ([
			'sales_order_grid',
			'sales_shipment_grid', 
			'sales_invoice_grid', 
			'sales_creditmemo_grid'] as $table) {
			$column = ($table == 'sales_order_grid') ? self::COLUMN_ENTITY_ID : 'order_id';
			$condition = $this->_connection->quoteInto(
				$column . ' IN (?)', 
				$toDelete
			);
			$this->_connection->delete(
				$this->_resource->getTableName($table), 
				$condition
			);
		}
        return $this;
    }
    
    /**
     * Prepare Status
     *
     * @param array $rowData
     * @return void
     */
    public function prepareStatus(array $rowData)
    {
		if (empty($rowData['status']) || empty($rowData['status_label'])) {
			return;
		}
		if (null === $this->_status) {
			$this->_status = $this-> _getStatusCollection();
		}
		if (!in_array($rowData['status'], $this->_status)) {
			$this->saveStatus($rowData['status'], $rowData['status_label']);
		}
    }
    
    /**
     * Save Status
     *
     * @param string $status
     * @param string $label    
     * @return void
     */
    public function saveStatus($status, $label)
    {
		$this->_status[] = $status;
		$this->_connection->insert(
			$this->getStatusTable(), 
			['status' => $status, 'label' => $label]
		);
    }
    
    /**
     * Retrieve Status Collection
     *
     * @return array
     */
    protected function _getStatusCollection()
    {
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->_connection->select();
        $select->from($this->getStatusTable(), 'status');
        
        return $this->_connection->fetchCol($select);
    }
    
    /**
     * Retrieve Status Table Name
     *
     * @return string
     */
    public function getStatusTable()
    {
        return $this->_resource->getTableName(
			$this->_statusTable
		);  
    } 
}
