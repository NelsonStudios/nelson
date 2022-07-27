<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


/**
 * Copyright © 2017 Aitoc. All rights reserved.
 */

namespace Aitoc\DimensionalShipping\Controller\Adminhtml\Orderitems;


/**
 * Class Changebox
 *
 * @package Aitoc\DimensionalShipping\Controller\Adminhtml\Orderitems
 */
class Changebox extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;

    /**
     * @var BoxRepository
     */
    private $boxRepository;

    /**
     * @var \Aitoc\DimensionalShipping\Model\OrderItemBoxRepository
     */
    private $orderBoxItemRepository;

    /**
     * @var \Aitoc\DimensionalShipping\Model\OrderBoxRepository
     */
    private $orderBoxRepository;

    /**
     * @var \Aitoc\DimensionalShipping\Model\ResourceModel\OrderItemBox\CollectionFactory
     */
    private $orderBoxItemCollectionFactory;

    /**
     * @var \Aitoc\DimensionalShipping\Model\ResourceModel\OrderBox\CollectionFactory
     */
    private $orderBoxCollectionFactory;
    /**
     * @var \Magento\Sales\Model\Order\ItemRepository
     */
    private $itemRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    private $orderItemCollectionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Aitoc\DimensionalShipping\Model\BoxRepository $boxRepository,
        \Aitoc\DimensionalShipping\Model\OrderItemBoxRepository $orderBoxItemRepository,
        \Aitoc\DimensionalShipping\Model\OrderBoxRepository $orderBoxRepository,
        \Aitoc\DimensionalShipping\Model\ResourceModel\OrderBox\CollectionFactory $orderBoxCollectionFactory,
        \Aitoc\DimensionalShipping\Model\ResourceModel\OrderItemBox\CollectionFactory $orderBoxItemCollectionFactory,
        \Magento\Sales\Model\Order\ItemRepository $itemRepository,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->boxRepository = $boxRepository;
        $this->orderBoxItemRepository = $orderBoxItemRepository;
        $this->orderBoxRepository = $orderBoxRepository;
        $this->orderBoxItemCollectionFactory = $orderBoxItemCollectionFactory;
        $this->orderBoxCollectionFactory = $orderBoxCollectionFactory;
        $this->itemRepository = $itemRepository;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultRaw = $this->resultRawFactory->create();
        $httpBadRequestCode = 400;
        if ($this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        $orderItemBoxId = $this->getRequest()->getParam('order_item_box_id');
        $orderItemId = $this->getRequest()->getParam('order_item_id');
        $sku = $this->getRequest()->getParam('sku');
        $orderBoxId = $this->getRequest()->getParam('order_box_id');
        $newBox = $this->getRequest()->getParam('new_element');
        $orderId = $this->getRequest()->getParam('order_id');
        $qty = $this->getRequest()->getParam('qty_boxed');
        $notPacked = $this->getRequest()->getParam('not_packed');
        if ($newBox == 1) {
            $orderBoxModel = $this->orderBoxRepository->create();
            $orderBoxModel->setOrderId($orderId);
            $orderBoxModel->setBoxId($orderBoxId);
            $newOrderBox = $this->orderBoxRepository->save($orderBoxModel);
        }
        if (!$notPacked) {
            if ($qty > 1) {
                $orderBoxItemCollection = $this->orderBoxItemCollectionFactory->create();
                $condition = "`order_id`= {$orderId} AND `sku`= '{$sku}'";
                $orderBoxItemCollection->setTableRecords(
                    $condition,
                    ['order_box_id' => $newBox == 1 ? $newOrderBox->getItemId() : $orderBoxId],
                    $qty
                )->getItems();
            } else {
                if (!$orderItemId || !$orderBoxId) {
                    return $resultRaw->setHttpResponseCode($httpBadRequestCode);
                }
                $orderBoxItemModel = $this->orderBoxItemRepository->get($orderItemBoxId);
                $orderBoxItemModel->setOrderBoxId($newBox == 1 ? $newOrderBox->getItemId() : $orderBoxId)->save();
            }
        } else {
            if (!$qty) {
                $qty = 1;
            }
            $itemCollectionCount = $this->orderBoxItemCollectionFactory
                ->create()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('sku', $sku)
                ->addFieldToFilter('not_packed', 1)
                ->count();
            if ($qty > round($itemCollectionCount)) {
                $response = [
                    'errors' => true,
                    'message' => __('The qty is incorrect.')
                ];
                $resultJson = $this->resultJsonFactory->create();

                return $resultJson->setData($response);
            }
            $orderBoxItemCollection = $this->orderBoxItemCollectionFactory
                ->create()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('sku', $sku)
                ->addFieldToFilter('not_packed', 1)
                ->setLimit($qty)
                ->getItems();

            foreach ($orderBoxItemCollection as $orderBoxItem) {
                $orderBoxItemModel = $this->orderBoxItemRepository->get($orderBoxItem->getId());
                $orderBoxItemModel->setOrderBoxId($newBox == 1 ? $newOrderBox->getItemId() : $orderBoxId);
                $orderBoxItemModel->setNotPacked(0);
                $this->orderBoxItemRepository->save($orderBoxItemModel);
            }
        }
        $response = [
            'errors' => false,
            'message' => __('Box changed successful.')
        ];
        $resultJson = $this->resultJsonFactory->create();

        //Check the boxes for the number of elements in them, if empty box do delete
        $orderBoxCollection = $this->orderBoxCollectionFactory->create();
        $orderBoxCollection->addFieldToFilter('order_id', $orderId)->getItems();
        foreach ($orderBoxCollection as $boxOrder) {
            $orderBoxItemCollection = $this->orderBoxItemCollectionFactory->create();
            $countItemsInBox = $orderBoxItemCollection
                ->addFieldToFilter('order_box_id', $boxOrder->getId())
                ->addFieldToFilter('order_id', $orderId)
                ->count();
            if ($countItemsInBox == 0) {
                $this->orderBoxRepository->deleteById($boxOrder->getId());
            }
        }

        //Recalculate weight in all boxes
        $orderBoxCollection = $this->orderBoxCollectionFactory->create()
            ->addFieldToFilter('order_id', $orderId)
            ->getItems();

        $newOrderWeight = 0;
        foreach ($orderBoxCollection as $orderBox) {
            $boxWeight = (float)$this->boxRepository->get($orderBox->getBoxId())->getEmptyWeight();
            $orderBoxItemCollection = $this->orderBoxItemCollectionFactory->create()
                ->addFieldToFilter('order_box_id', $orderBox->getId())->getItems();
            foreach ($orderBoxItemCollection as $orderBoxItem) {
                $orderItem = $this->itemRepository->get($orderBoxItem->getOrderItemId());
                $boxWeight += (float)$orderItem->getWeight();
            }
            $orderBoxItemModel = $this->orderBoxRepository->get($orderBox->getId());
            $orderBoxItemModel->setWeight($boxWeight);
            $this->orderBoxRepository->save($orderBoxItemModel);
            $newOrderWeight += $boxWeight;
        }

        $orderBoxItemCollection = $this->orderBoxItemCollectionFactory->create()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('not_packed', 1);
        foreach ($orderBoxItemCollection as $orderBoxItem) {
            $orderItem = $this->itemRepository->get($orderBoxItem->getOrderItemId());
            $newOrderWeight += (float)$orderItem->getWeight();
        }
        $order = $this->orderRepository->get($orderId)->setWeight($newOrderWeight);
        $this->orderRepository->save($order);

        return $resultJson->setData($response);
    }
}
