<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExport\Model\Import\Order;

use Magento\ImportExport\Model\Import;

/**
 * Order Creditmemo Import
 */
class Creditmemo extends AbstractAdapter
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
    const COLUMN_ORDER_ID = 'order_id'; 
    
    /**
     * Increment Id Column Name
     *
     */
    const COLUMN_INCREMENT_ID = 'increment_id';     
    
    /**
     * Error Codes
     */       
	const ERROR_ENTITY_ID_IS_EMPTY = 'creditmemoEntityIdIsEmpty';
	const ERROR_ORDER_ID_IS_EMPTY = 'creditmemoOrderIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateCreditmemoEntityId';
    const ERROR_DUPLICATE_INCREMENT_ID = 'duplicateCreditmemoIncrementId';    
	const ERROR_INCREMENT_ID_IS_EMPTY = 'creditmemoIncrementIdIsEmpty';	
	
    /**
     * Validation Failure Message Template Definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Creditmemo entity_id is found more than once in the import file',
        self::ERROR_DUPLICATE_INCREMENT_ID => 'Creditmemo increment_id is found more than once in the import file',
        self::ERROR_ORDER_ID_IS_EMPTY => 'Creditmemo order_id is empty',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Creditmemo entity_id is empty',
        self::ERROR_INCREMENT_ID_IS_EMPTY => 'Creditmemo increment_id is empty',        
    ];
    
    /**
     * Order Creditmemo Table Name
     *
     * @var string
     */
    protected $_mainTable = 'sales_creditmemo';  
	
    /**
     * Retrieve The Prepared Data
     *
     * @param array $rowData
     * @return array|bool
     */
    public function prepareRowData(array $rowData)
    {
		$rowData = $this->_extractField($rowData, 'creditmemo');
		return (count($rowData) && !$this->isEmptyRow($rowData)) 
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
     * Retrieve Shipping Address Id
     *
     * @param array $rowData     
     * @return bool|int
     */
    protected function _getShippingAddressId($rowData)
    {
        $bind = [
			':address_type' => 'shipping',
			':parent_id' => $this->_getOrderId($rowData)
		];
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->_connection->select();
        $select->from($this->getAddressTable(), 'entity_id')
			->where('parent_id = :parent_id')
			->where('address_type = :address_type');
        
        return $this->_connection->fetchOne($select, $bind);
    } 
    
    /**
     * Retrieve Billing Address Id
     *
     * @param array $rowData     
     * @return bool|int
     */
    protected function _getBillingAddressId($rowData)
    {
        $bind = [
			':address_type' => 'billing',
			':parent_id' => $this->_getOrderId($rowData)
		];
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->_connection->select();
        $select->from($this->getAddressTable(), 'entity_id')
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

        list($createdAt, $updatedAt) = $this->_prepareDateTime($rowData);

        $newEntity = false;
        $entityId = $this->_getExistEntityId($rowData);
        if (!$entityId) {
            /* create new entity id */
            $newEntity = true;
            $entityId = $this->_getNextEntityId();
            $this->_newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;
        }
		
        $this->creditmemoIdsMap[$this->_getEntityId($rowData)] = $entityId;
		
		$entityRow = [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'order_id' => $this->_getOrderId($rowData),
            'shipping_address_id' => $this->_getShippingAddressId($rowData),
            'billing_address_id' => $this->_getBillingAddressId($rowData),            
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
            
			if (empty($rowData[self::COLUMN_ORDER_ID])) {
				$this->addRowError(self::ERROR_ORDER_ID_IS_EMPTY, $rowNumber);
			} 
        }
    } 
}
