<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExport\Model\Import;

use Magento\Sales\Model\ResourceModel\GridPool;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\AbstractSource;
use Firebear\ImportExport\Model\Import\ImportAdapterInterface;
use Firebear\ImportExport\Model\Import\Order\EntityFactory;
use Firebear\ImportExport\Model\Import\Order\ItemFactory;
use Firebear\ImportExport\Model\Import\Order\AddressFactory;
use Firebear\ImportExport\Model\Import\Order\ShipmentFactory;
use Firebear\ImportExport\Model\Import\Order\Shipment\ItemFactory as ShipmentItemFactory;
use Firebear\ImportExport\Model\Import\Order\Shipment\TrackFactory as ShipmentTrackFactory;
use Firebear\ImportExport\Model\Import\Order\Shipment\CommentFactory as ShipmentCommentFactory;
use Firebear\ImportExport\Model\Import\Order\PaymentFactory;
use Firebear\ImportExport\Model\Import\Order\Payment\TransactionFactory;
use Firebear\ImportExport\Model\Import\Order\InvoiceFactory;
use Firebear\ImportExport\Model\Import\Order\Invoice\ItemFactory as InvoiceItemFactory;
use Firebear\ImportExport\Model\Import\Order\Invoice\CommentFactory as InvoiceCommentFactory;
use Firebear\ImportExport\Model\Import\Order\CreditmemoFactory;
use Firebear\ImportExport\Model\Import\Order\Creditmemo\ItemFactory as CreditmemoItemFactory;
use Firebear\ImportExport\Model\Import\Order\Creditmemo\CommentFactory as CreditmemoCommentFactory;
use Firebear\ImportExport\Model\Import\Order\TaxFactory;
use Firebear\ImportExport\Model\Import\Order\Tax\ItemFactory as TaxItemFactory;
use Firebear\ImportExport\Model\Import\Order\Status\HistoryFactory as StatusHistoryFactory;
use Firebear\ImportExport\Model\Import\Order\DataProcessor;
use Firebear\ImportExport\Model\Import\Context;
use Firebear\ImportExport\Traits\General as GeneralTrait;

/**
 * Order Import
 */
class Order extends AbstractEntity implements ImportAdapterInterface
{
    use GeneralTrait;
    
    /**
     * Entity Type Code
     *
     */
    const ENTITY_TYPE_CODE = 'order';
    
    /**
     * Order Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Entity
     */
    protected $_order;
    
    /**
     * Item Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Item
     */
    protected $_item;
    
    /**
     * Address Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Address
     */
    protected $_address; 
    
    /**
     * Shipment Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Shipment
     */
    protected $_shipment; 
	
    /**
     * Shipment Item Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Shipment\Item
     */
    protected $_shipmentItem; 
    
    /**
     * Shipment Track Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Shipment\Track
     */
    protected $_shipmentTrack; 
    
    /**
     * Shipment Comment Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Shipment\Comment
     */
    protected $_shipmentComment; 
    
    /**
     * Payment Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Payment
     */
    protected $_payment;  
    
    /**
     * Payment Transaction Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Payment\Transaction
     */
    protected $_transaction; 
    
    /**
     * Invoice Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Invoice
     */
    protected $_invoice; 
	
    /**
     * Invoice Item Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Invoice\Item
     */
    protected $_invoiceItem;
    
    /**
     * Invoice Comment Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Invoice\Comment
     */
    protected $_invoiceComment; 
    
    /**
     * Creditmemo Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Creditmemo
     */
    protected $_creditmemo; 
	
    /**
     * Creditmemo Item Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Creditmemo\Item
     */
    protected $_creditmemoItem; 
    
    /**
     * Creditmemo Comment Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Creditmemo\Comment
     */
    protected $_creditmemoComment; 
    
    /**
     * Tax Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Tax
     */
    protected $_tax;
    
    /**
     * Tax Item Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Tax\Item
     */
    protected $_taxItem; 
    
    /**
     * Status History Entity Adapter
     *
     * @var \Firebear\ImportExport\Model\Import\Order\Status\History
     */
    protected $_statusHistory; 
    
    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    
    /**
     * Grid Pool
     *
     * @var \Magento\Sales\Model\ResourceModel\GridPool
     */
    protected $_gridPool; 
    
    /**
     * Data Processor
     *
     * @var \Firebear\ImportExport\Model\Import\Order\DataProcessor
     */
    protected $_dataProcessor;     
    
    /**
     * Initialize Import
	 *
     * @param Context $context
     * @param EntityFactory $entityFactory
     * @param ItemFactory $itemFactory
     * @param AddressFactory $addressFactory     
     * @param ShipmentFactory $shipmentFactory  
     * @param ShipmentItemFactory $shipmentItemFactory
     * @param ShipmentTrackFactory $shipmentTrackFactory
     * @param ShipmentCommentFactory $shipmentCommentFactory 
     * @param PaymentFactory $paymentFactory 
     * @param TransactionFactory $transactionFactory      
     * @param InvoiceFactory $invoiceFactory  
     * @param InvoiceItemFactory $invoiceItemFactory
     * @param InvoiceCommentFactory $invoiceCommentFactory     
     * @param CreditmemoFactory $creditmemoFactory  
     * @param CreditmemoItemFactory $creditmemoItemFactory
     * @param CreditmemoCommentFactory $creditmemoCommentFactory
     * @param TaxFactory $taxFactory
     * @param TaxItemFactory $taxItemFactory     
     * @param StatusHistoryFactory $statusHistory   
     * @param GridPool $gridPool
     * @param DataProcessor $dataProcessor  
     */
    public function __construct(
        Context $context,
        EntityFactory $entityFactory,
        ItemFactory $itemFactory,
        AddressFactory $addressFactory,
        ShipmentFactory $shipmentFactory,
		ShipmentItemFactory $shipmentItemFactory, 
		ShipmentTrackFactory $shipmentTrackFactory,
		ShipmentCommentFactory $shipmentCommentFactory,
        PaymentFactory $paymentFactory,
        TransactionFactory $transactionFactory,        
        InvoiceFactory $invoiceFactory,
		InvoiceItemFactory $invoiceItemFactory,
		InvoiceCommentFactory $invoiceCommentFactory,
        CreditmemoFactory $creditmemoFactory,
		CreditmemoItemFactory $creditmemoItemFactory,
		CreditmemoCommentFactory $creditmemoCommentFactory,
		TaxFactory $taxFactory,
		TaxItemFactory $taxItemFactory,
		StatusHistoryFactory $statusHistory,
        GridPool $gridPool,
        DataProcessor $dataProcessor
    ) {
        $this->_logger = $context->getLogger();
        $this->_order = $entityFactory->create();
        $this->_item = $itemFactory->create();
        $this->_address = $addressFactory->create();
        $this->_shipment = $shipmentFactory->create();
		$this->_shipmentItem = $shipmentItemFactory->create();
		$this->_shipmentTrack = $shipmentTrackFactory->create();
		$this->_shipmentComment = $shipmentCommentFactory->create(); 
        $this->_payment = $paymentFactory->create();
        $this->_transaction = $transactionFactory->create();        
        $this->_invoice = $invoiceFactory->create();
		$this->_invoiceItem = $invoiceItemFactory->create();
		$this->_invoiceComment = $invoiceCommentFactory->create(); 
        $this->_creditmemo = $creditmemoFactory->create();
		$this->_creditmemoItem = $creditmemoItemFactory->create();
		$this->_creditmemoComment = $creditmemoCommentFactory->create(); 
		$this->_tax = $taxFactory->create();
		$this->_taxItem = $taxItemFactory->create();
		$this->_statusHistory = $statusHistory->create(); 
        $this->_gridPool = $gridPool;
        $this->_dataProcessor = $dataProcessor;
		
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
    }
    
    /**
     * Retrieve All Fields Source
     *
     * @return array
     */
    public function getAllFields()
    {
        return $this->_order->getAllFields();  
    }
    
    /**
     * Retrieve Children Adapters
     *
     * @return array
     */
    public function getChildren()
    {
        return [
			$this->_order,
			$this->_item,
			$this->_address,
			$this->_shipment,
			$this->_shipmentItem,	
			$this->_shipmentTrack,
			$this->_shipmentComment,
			$this->_payment,
			$this->_transaction,
			$this->_invoice,
			$this->_invoiceItem,
			$this->_invoiceComment,
			$this->_creditmemo,
			$this->_creditmemoItem,
			$this->_creditmemoComment,
			$this->_tax,
			$this->_taxItem,
			$this->_statusHistory
        ];  
    }
    
    /**
     * Import Data Rows
     *
     * @return boolean
     */
    protected function _importData() 
	{		
		$this->_dataProcessor->setFileName(
			$this->_dataSourceModel->getFile()
		);
		/* import data */
		$this->_connection->beginTransaction();
		try {
			if ($this->_order->importData()) {			
				list($orderIds, $orderItemIds) = $this->_importOrderItem();
				$this->_importAddress($orderIds);	
				$this->_importShipment($orderIds, $orderItemIds);
				$this->_importPayment($orderIds);	
				$this->_importInvoice($orderIds, $orderItemIds);	
				$this->_importCreditmemo($orderIds, $orderItemIds);
				$this->_importTax($orderIds, $orderItemIds);
				$this->_importStatusHistory($orderIds);
				/* refresh grid and grid archive(ee) */
				foreach ($orderIds as $orderId) {
					$this->_gridPool->refreshByOrderId($orderId);
				}
			}
			$this->_connection->commit();
		} catch (\Exception $e) {
			$this->_connection->rollBack();
		}
		return true;
    } 
    
    /**
     * Import Order Item Data
     *    
     * @return array
     */
    protected function _importOrderItem()
    {
		$orderIds = $this->_dataProcessor->merge(
			$this->_order->getOrderIdsMap(), 
			'orderIds'
		);
		/* order item */
		$this->_item->setOrderIdsMap($orderIds)
			->importData();
			
		$orderItemIds = $this->_dataProcessor->merge(
			$this->_item->getItemIdsMap(), 
			'orderItemIds'
		);
		return [$orderIds, $orderItemIds];
    } 
    
    /**
     * Import Address Data
     *
     * @param array $orderIds    
     * @return void
     */
    protected function _importAddress(array $orderIds)
    {
		$this->_address->setOrderIdsMap($orderIds)
			->importData();
    }    
    
    /**
     * Import Shipment Data
     *
     * @param array $orderIds
     * @param array $orderItemIds     
     * @return void
     */
    protected function _importShipment(array $orderIds, array $orderItemIds)
    {
		$this->_shipment->setOrderIdsMap($orderIds)->importData();			
		$shipmentIds = $this->_dataProcessor->merge(
			$this->_shipment->getShipmentIdsMap(),
			'shipmentIds'
		);
		/* shipment item */
		$this->_shipmentItem
			->setShipmentIdsMap($shipmentIds)
			->setItemIdsMap($orderItemIds)				
			->importData();	
		/* shipment track */	
		$this->_shipmentTrack
			->setShipmentIdsMap($shipmentIds)
			->setOrderIdsMap($orderIds)				
			->importData();	
		/* shipment comment */	
		$this->_shipmentComment	
			->setShipmentIdsMap($shipmentIds)			
			->importData(); 
    }
    
    /**
     * Import Payment Data
     *
     * @param array $orderIds    
     * @return void
     */
    protected function _importPayment(array $orderIds)
    {
		$this->_payment->setOrderIdsMap($orderIds)->importData();
		$paymentIds = $this->_dataProcessor->merge(
			$this->_payment->getPaymentIdsMap(),
			'paymentIds'
		);			
		/* transaction */
		$transactionIds = $this->_dataProcessor->load('transactionIds');
		$this->_transaction
			->setOrderIdsMap($orderIds)
			->setPaymentIdsMap($paymentIds)
			->setTransactionIdsMap($transactionIds)
			->importData();
			
		$this->_dataProcessor->merge(
			$this->_transaction->getTransactionIdsMap(),
			'transactionIds'
		);				
    }
    
    /**
     * Import Invoice Data
     *
     * @param array $orderIds
     * @param array $orderItemIds     
     * @return void
     */
    protected function _importInvoice(array $orderIds, array $orderItemIds)
    {
		$this->_invoice->setOrderIdsMap($orderIds)->importData();
		$invoiceIds = $this->_dataProcessor->merge(
			$this->_invoice->getInvoiceIdsMap(),
			'invoiceIds'
		);				
		/* invoice item */
		$this->_invoiceItem
			->setInvoiceIdsMap($invoiceIds)
			->setItemIdsMap($orderItemIds)
			->importData();
		/* invoice comment */	
		$this->_invoiceComment	
			->setInvoiceIdsMap($invoiceIds)			
			->importData();  
    }
    
    /**
     * Import Creditmemo Data
     *
     * @param array $orderIds
     * @param array $orderItemIds     
     * @return void
     */
    protected function _importCreditmemo(array $orderIds, array $orderItemIds)
    {
		$this->_creditmemo->setOrderIdsMap($orderIds)->importData();
		$creditmemoIds = $this->_dataProcessor->merge(
			$this->_creditmemo->getCreditmemoIdsMap(),
			'creditmemoIds'
		);
		/* creditmemo item */
		$this->_creditmemoItem
			->setCreditmemoIdsMap($creditmemoIds)
			->setItemIdsMap($orderItemIds)
			->importData();
		/* creditmemo comment */	
		$this->_creditmemoComment	
			->setCreditmemoIdsMap($creditmemoIds)			
			->importData();	    
    }
    
    /**
     * Import Tax Data
     *
     * @param array $orderIds
     * @param array $orderItemIds     
     * @return void
     */
    protected function _importTax(array $orderIds, array $orderItemIds)
    {
		$this->_tax->setOrderIdsMap($orderIds)->importData();
		$taxIds = $this->_dataProcessor->merge(
			$this->_tax->getTaxIdsMap(),
			'taxIds'
		); 
		/* tax item */
		$this->_taxItem
			->setTaxIdsMap($taxIds)
			->setItemIdsMap($orderItemIds)
			->importData();	
    }
    
    /**
     * Import Status History Data
     *
     * @param array $orderIds   
     * @return void
     */
    protected function _importStatusHistory(array $orderIds)
    {
		$this->_statusHistory->setOrderIdsMap($orderIds)->importData();  
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
		foreach ($this->getChildren() as $adapter) {
			$tempData = $adapter->prepareRowData($rowData);
			if ($tempData && !$adapter->validateRow($tempData, $rowNumber)) {
				return false;
			}
		} 
		return true;
    }
    
    /**
     * Retrieve Entity Type Code
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
		return self::ENTITY_TYPE_CODE;
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
                if ($this->validateRow($rowData, $source->key())) {
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
                }
                $source->next();
            }
        }
        return $this;
    }
    
    /**
     * Output Model Setter
     *
     * @param $output
     * @return $this
     */    
    public function setOutput($output)
    {
        $this->output = $output;
		foreach ($this->getChildren() as $adapter) {
			$adapter->setOutput($output);
		}        
        return $this;
    }
    
    /**
     * Logger Model Setter
     *
     * @param $logger
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->_logger = $logger;
		foreach ($this->getChildren() as $adapter) {
			$adapter->setLogger($logger);
		}
        return $this;
    }
    
    /**
     * Set Data From Outside To Change Behavior
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
		parent::setParameters($parameters);		
		foreach ($this->getChildren() as $adapter) {
			$adapter->setParameters($parameters);
		}
        return $this;
    }
    
    /**
     * Source Model Setter
     *
     * @param AbstractSource $source
     * @return \Magento\ImportExport\Model\Import\AbstractEntity
     */
    public function setSource(AbstractSource $source)
    {
		foreach ($this->getChildren() as $adapter) {
			$adapter->setSource($source);
		}
        return parent::setSource($source);
    } 
    
    /**
     * Error Aggregator Setter
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator     
     * @param $this
     */
    public function setErrorAggregator($errorAggregator)
    {
        $this->errorAggregator = $errorAggregator;
		foreach ($this->getChildren() as $adapter) {
			$adapter->setErrorAggregator($errorAggregator);
			$adapter->initErrorTemplates();
		}
        return $this;
    }    
    
    /**
     * Returns Number Of Checked Entities
     *
     * @return int
     */
    public function getProcessedEntitiesCount()
    {
		$this->_processedEntitiesCount = $this->_order->getProcessedEntitiesCount();
		foreach ($this->getChildren() as $adapter) {
			$this->_processedEntitiesCount = max(
				$this->_processedEntitiesCount,
				$adapter->getProcessedEntitiesCount()
			);
		} 
        return $this->_processedEntitiesCount;
    }   
}
