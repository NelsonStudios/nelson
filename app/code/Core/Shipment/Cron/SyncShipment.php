<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Core\Shipment\Cron;

class SyncShipment
{

    protected $logger;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $shipmentTrackFactory,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->logger = $logger;
        $this->_shipmentTrackFactory = $shipmentTrackFactory;
        $this->_shipmentFactory = $shipmentFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_orderRepository = $orderRepository;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $date = date("Y-m-d");
        $shipmentData = file_get_contents("https://feconslws.fecon.com/FeconSLWebService.asmx/getEShipments?date=".$date);
        $shipmentData = json_decode($shipmentData, true);
        foreach ($shipmentData as $orderData) {
            if(isset($orderData['OrderNum']) && $orderData['OrderNum']) {
                try {
                    $order = $this->getOrderByExtenalId($orderData['OrderNum']);
                    if(!is_null($order) && !$this->isTracked($order->getId(), $orderData['TrackingNum'])) {
                        try {
                            $data[] = [
                                'carrier_code' => $order->getShippingMethod(),
                                'title' => $order->getShippingDescription(),
                                'number' => $orderData['TrackingNum']
                            ];
                            $shipment = $this->prepareShipment($order, $data, $orderData);

                            if ($shipment) {
                                $order->setIsInProcess(true);
                                $order->addStatusHistoryComment('Automatically SHIPPED', false);
                                $transactionSave =  $this->_transactionFactory->create()->addObject($shipment)->addObject($shipment->getOrder());
                                $transactionSave->save();
                            }
                        } catch (\Exception $e) {
                            $this->logger->debug(json_encode($orderData));
                            $this->logger->debug($e->getMessage());
                        }
                    }
                } catch (Exception $e) {
                    $this->logger->debug(json_encode($orderData));
                    $this->logger->debug($e->getMessage());
                }
            }
        }
    }

    protected function getOrderByExtenalId($extId)
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orderCollection = $objectManager->create('Magento\Sales\Model\Order')->getCollection();
            $orderCollection->addFieldToSelect('*');
            $orderCollection->addFieldToFilter('syteline_id', ['eq' => $extId]);

            return $orderCollection->getFirstItem();
        } catch (Exception $e) {
            $this->logger->debug("Can not find order " . $extId);
            $this->logger->debug($e->getMessage());
            return null;
        }

        return null;
    }
    
    private function isTracked($orderId, $trackNumber)
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $sql = "Select * FROM sales_shipment_track where order_id =" . $orderId . " and track_number = '".$trackNumber . "'";
            $result = $connection->fetchOne($sql);

            return $result;
        } catch (Exception $e) {
            $this->logger->debug("Can not find track " . $orderId . " " . $trackNumber);
            $this->logger->debug($e->getMessage());
            return false;
        }

        return false;
    }
    
    /**
     * @param $order \Magento\Sales\Model\Order
     * @param $track array
     * @return $this
     */
    protected function prepareShipment($order, $track, $orderData)
    {
        $shipment = $this->_shipmentFactory->create(
            $order,
            $this->prepareShipmentItems($order, $orderData),
            $track
        );
        return $shipment->getTotalQty() ? $shipment->register() : false;
    }
    
    /**
     * @param $order \Magento\Sales\Model\Order
     * @return array
     */
    protected function prepareShipmentItems($order, $orderData)
    {
        $items = [];

        foreach($order->getAllItems() as $item) {
            if($item->getSku() == $orderData['Item']) {
                $items[$item->getItemId()] = $orderData['Qty'];
            }
        }

        return $items;
    }
}

