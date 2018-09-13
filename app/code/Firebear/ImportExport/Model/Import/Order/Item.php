<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExport\Model\Import\Order;

use Magento\ImportExport\Model\Import;

/**
 * Order Item Import
 */
class Item extends AbstractAdapter
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
    const COLUMN_ENTITY_ID = 'item_id';

    /**
     * Order Id Column Name
     *
     */
    const COLUMN_ORDER_ID = 'order_id'; 
    
    /**
     * Quote Item Id Column Name
     *
     */
    const COLUMN_QUOTE_ITEM_ID = 'quote_item_id';    
    
    /**
     * Product Id Column Name
     *
     */
    const COLUMN_PRODUCT_ID = 'product_id'; 
    
    /**
     * Product Options Column Name
     *
     */
    const COLUMN_PRODUCT_OPTIONS = 'product_options';
    
    /**
     * Parent Item Id Column Name
     *
     */
    const COLUMN_PARENT_ITEM_ID = 'parent_item_id';
    
    /**
     * Created At Column Name
     *
     */
    const COLUMN_CREATED_AT = 'created_at'; 
    
    /**
     * Updated At Column Name
     *
     */
    const COLUMN_UPDATED_AT = 'updated_at'; 
    
    /**
     * Sku Column Name
     *
     */
    const COLUMN_SKU = 'sku'; 
    
    /**
     * Error Codes
     */       
	const ERROR_ENTITY_ID_IS_EMPTY = 'orderItemIdIsEmpty';
	const ERROR_ORDER_ID_IS_EMPTY = 'orderItemOrderIdIsEmpty';
	const ERROR_PRODUCT_ID_IS_EMPTY = 'orderItemProductIdIsEmpty';	
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateOrderItemId';	
	
    /**
     * Validation Failure Message Template Definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Order Item item_id is found more than once in the import file',
        self::ERROR_ORDER_ID_IS_EMPTY => 'Order Item order_id is empty',
        self::ERROR_PRODUCT_ID_IS_EMPTY => 'Order Item product_id is empty',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Order Item entity_id is empty'
    ];
    
    /**
     * Order Item Table Name
     *
     * @var string
     */
    protected $_mainTable = 'sales_order_item';  
    
    /**
     * Retrieve The Prepared Data
     *
     * @param array $rowData
     * @return array|bool
     */
    public function prepareRowData(array $rowData)
    {
		if (empty($rowData['orders_item'])) {
			return false;
		}		
		$options = $this->_getProductOptions($rowData);
		$rowData = $this-> _explodeField($rowData['orders_item']);
		$rowData[self::COLUMN_PRODUCT_OPTIONS] = $options;

		return $rowData;
    }

    /**
     * Retrieve The Product Options
     *
     * @param array $rowData
     * @return array|bool
     */
    protected function _getProductOptions(array $rowData)
    {
		return isset($rowData['orders_item_product_options'])
			? $rowData['orders_item_product_options']
			: null;
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
			':product_id' => $rowData[self::COLUMN_PRODUCT_ID],
			':product_options' => $rowData[self::COLUMN_PRODUCT_OPTIONS],
			':order_id' => $this->_getOrderId($rowData)
		];
        $select = $this->_connection->select();
        $select->from($this->getMainTable(), 'item_id')
			->where('product_id = :product_id')
			->where('product_options = :product_options')
			->where('order_id = :order_id');

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
        
        $quoteItemId = null;
        if (!empty($rowData[self::COLUMN_QUOTE_ITEM_ID])) {
			$quoteItemId = $rowData[self::COLUMN_QUOTE_ITEM_ID];
        }
        
        $parentItemId = null;
        if (!empty($rowData[self::COLUMN_PARENT_ITEM_ID])) {
            if (isset($this->itemIdsMap[$rowData[self::COLUMN_PARENT_ITEM_ID]])) {
				$parentItemId = $this->itemIdsMap[$rowData[self::COLUMN_PARENT_ITEM_ID]];
            }
        }
        
        $productId = null;
        if (!empty($rowData[self::COLUMN_SKU])) {
			$productId = $this->getProductIdBySku($rowData[self::COLUMN_SKU]) ?: null;
        }         

        $newEntity = false;
        $entityId = $this->_getExistEntityId($rowData);
        $orderId = $this->_getOrderId($rowData);
        
        if (!$entityId  && $rowData[self::COLUMN_ORDER_ID] != $orderId) {
            /* create new entity id */
            $newEntity = true;
            $entityId = $this->_getNextEntityId();
            $this->_newEntities[$rowData[self::COLUMN_ENTITY_ID]] = $entityId;
        }

		$this->itemIdsMap[$rowData[self::COLUMN_ENTITY_ID]] = $entityId;        
        
        $entityRow = [
            self::COLUMN_CREATED_AT => $createdAt,
            self::COLUMN_UPDATED_AT => $updatedAt,
            self::COLUMN_QUOTE_ITEM_ID => $quoteItemId,
			self::COLUMN_ORDER_ID => $orderId,
            self::COLUMN_ENTITY_ID => $entityId,
            self::COLUMN_PARENT_ITEM_ID => $parentItemId,
            self::COLUMN_PRODUCT_ID => $productId
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
        if ($this->_checkEntityIdKey($rowData, $rowNumber)) {
			if (empty($rowData[self::COLUMN_ORDER_ID])) {
				$this->addRowError(self::ERROR_ORDER_ID_IS_EMPTY, $rowNumber);
			} 
			
			if (empty($rowData[self::COLUMN_PRODUCT_ID])) {
				$this->addRowError(self::ERROR_PRODUCT_ID_IS_EMPTY, $rowNumber);
			} 
        }
    }
}
