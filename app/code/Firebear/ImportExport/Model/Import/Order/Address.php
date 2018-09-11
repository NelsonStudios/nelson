<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExport\Model\Import\Order;

use Magento\ImportExport\Model\Import;

/**
 * Order Address Import
 */
class Address extends AbstractAdapter
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
     * Order Id Column Name
     *
     */
    const COLUMN_ORDER_ID = 'parent_id';    

    /**
     * Address Type Column Name
     *
     */
    const COLUMN_ADDRESS_TYPE = 'address_type';
    
    /**
     * Keys Which Used To Build Result Data Array For Future Update
     */
    const ENTITIES_TO_BILLING_KEY = 'entities_to_update_billing';  
    const ENTITIES_TO_SHIPPING_KEY = 'entities_to_update_shipping';
    
    /**
     * Error Codes
     */       
	const ERROR_ENTITY_ID_IS_EMPTY = 'addressEntityIdIsEmpty';
	const ERROR_ORDER_ID_IS_EMPTY = 'addressOrderIdIsEmpty';
	const ERROR_ADDRESS_TYPE_IS_EMPTY = 'addressTypeIsEmpty';	
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateAddressEntityId';	
	
    /**
     * Validation Failure Message Template Definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Address entity_id is found more than once in the import file',
        self::ERROR_ORDER_ID_IS_EMPTY => 'Address order_id is empty',
        self::ERROR_ADDRESS_TYPE_IS_EMPTY => 'Address address_type is empty',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Address entity_id is empty'
    ];
    
    /**
     * Order Address Table Name
     *
     * @var string
     */
    protected $_mainTable = 'sales_order_address';
    
    /**
     * Retrieve The Prepared Data
     *
     * @param array $rowData
     * @return array|bool
     */
    public function prepareRowData(array $rowData)
    {
		if (empty($rowData['orders_address'])) {
			return false;
		}		
		return $this-> _explodeField($rowData['orders_address']);
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
            $toBilling = [];
            $toShipping = [];
			
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
						$toDelete[] = $this->_getEntityId($rowData);
						break;
					case Import::BEHAVIOR_REPLACE:
						$data = $this->_prepareDataForReplace($rowData);
						$toUpdate = array_merge($toUpdate, $data[self::ENTITIES_TO_UPDATE_KEY]);
						break;
					case Import::BEHAVIOR_ADD_UPDATE:
						$data = $this->_prepareDataForUpdate($rowData);
						$toCreate = array_merge($toCreate, $data[self::ENTITIES_TO_CREATE_KEY]);
						$toUpdate = array_merge($toUpdate, $data[self::ENTITIES_TO_UPDATE_KEY]);
						$toBilling = array_merge($toBilling, $data[self::ENTITIES_TO_BILLING_KEY]);
						$toShipping = array_merge($toShipping, $data[self::ENTITIES_TO_SHIPPING_KEY]);						
						break;
				}
            } 
            /* save prepared data */
			if ($toCreate || $toUpdate) {
				$this->_saveEntities($toCreate, $toUpdate);
				$this->_updateOrderEntities($toBilling);
				$this->_updateOrderEntities($toShipping);
			}
			if ($toDelete) {
				$this->_deleteEntities($toDelete);
			}            
        }
        return true;
    }
    
    /**
     * Retrieve Entity Id If Entity Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function _getExistEntityId(array $rowData)
    {
        $bind = [
			':address_type' => $rowData[self::COLUMN_ADDRESS_TYPE],
			':parent_id' => $this->_getOrderId($rowData)
		];
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->_connection->select();
        $select->from($this->getMainTable(), 'entity_id')
			->where('parent_id = :parent_id')
			->where('address_type = :address_type');

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
        $toBilling = [];
        $toShipping = [];

        $newEntity = false;
        $entityId = $this->_getExistEntityId($rowData);
        $orderId = $this->_getOrderId($rowData);
        if (!$entityId && $rowData[self::COLUMN_ORDER_ID] != $orderId) {
            /* create new entity id */
            $newEntity = true;
            $entityId = $this->_getNextEntityId();
            $this->_newEntities[$rowData[self::COLUMN_ENTITY_ID]] = $entityId;
        }
        
		if ('billing' == $rowData[self::COLUMN_ADDRESS_TYPE]) {
			$toBilling[] = [
				'entity_id' => $orderId,
				'billing_address_id' => $entityId            
			];
		} else {
			$toShipping[] = [
				'entity_id' => $orderId,
				'shipping_address_id' => $entityId            
			];            
		}
            
        $entityRow = [
			'parent_id' => $orderId,
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
            self::ENTITIES_TO_UPDATE_KEY => $toUpdate,
            self::ENTITIES_TO_BILLING_KEY => $toBilling,
            self::ENTITIES_TO_SHIPPING_KEY => $toShipping
        ];		
    }
    
    /**
     * Update And Insert Data In Order Entity Table
     *
     * @param array $toOrder Rows for update
     * @return $this
     */
    protected function _updateOrderEntities(array $toOrder)
    {
        if ($toOrder) {
			foreach ($toOrder as $row) {
				$field = isset($row['billing_address_id']) 
					? 'billing_address_id' 
					: 'shipping_address_id';	
				$bind = [$field => $row[$field]];
				$where = ['entity_id = ?' => $row['entity_id']];			
				$this->_connection->update($this->getOrderTable(), $bind, $where);
			}
        }
        return $this;
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
        if ($this->_checkEntityIdKey($rowData, $rowNumber)) {
			if (empty($rowData[self::COLUMN_ORDER_ID])) {
				$this->addRowError(self::ERROR_ORDER_ID_IS_EMPTY, $rowNumber);
			} 
			
			if (empty($rowData[self::COLUMN_ADDRESS_TYPE])) {
				$this->addRowError(self::ERROR_ADDRESS_TYPE_IS_EMPTY, $rowNumber);
			} 
        }
    } 
}