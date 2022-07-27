<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


/**
 * Copyright © 2017 Aitoc. All rights reserved.
 */

namespace Aitoc\DimensionalShipping\Observer;

use Aitoc\DimensionalShipping\Helper\Data;
use Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker;
use Aitoc\DimensionalShipping\Model\BoxRepository;
use Aitoc\DimensionalShipping\Model\OrderBoxRepository;
use Aitoc\DimensionalShipping\Model\OrderItemBoxRepository;
use Aitoc\DimensionalShipping\Model\ResourceModel\Box\CollectionFactory;
use Aitoc\DimensionalShipping\Model\ResourceModel\ProductOptions\CollectionFactory as DimensionalShippingProductOptionsCollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\ItemRepository as OrderItemRepository;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;

class OrderPlaceAfter implements ObserverInterface
{
    protected $orderRepository;
    protected $helper;
    protected $packedOrderItemRepository;
    protected $orderBoxRepository;
    protected $orderItemRepository;

    const PACKAGE_ERROR_MESSAGE = 'The item(s) can`t be packed due to exclusive or unspecified dimensions and/or weight.';

    /**
     * @var \Aitoc\DimensionalShipping\Model\Packer
     */
    private $packer;

    public function __construct(
        OrderItemBoxRepository $packedOrderItemRepository,
        OrderItemRepository $orderItemRepository,
        OrderBoxRepository $orderBoxRepository,
        \Aitoc\DimensionalShipping\Model\Packer $packer
    ) {
        $this->packedOrderItemRepository = $packedOrderItemRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderBoxRepository = $orderBoxRepository;
        $this->packer = $packer;
    }

    /**
     * Box Packing Process.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        foreach ($this->packer->execute($order->getItems()) as $packedBox) {
            $orderBoxModel = $this->orderBoxRepository->create();
            $orderBoxModel
                ->setOrderId($order->getId())
                ->setBoxId($packedBox->getBox()->getBoxId())
                ->setWeight($packedBox->getWeight());
            $orderBoxModel = $this->orderBoxRepository->save($orderBoxModel);

            foreach ($packedBox->getItems() as $item) {
                $packedItem = $this->packedOrderItemRepository->create();
                $orderItem = $this->orderItemRepository->get($item->getItem()->getOrderItemId());
                $packedItem
                    ->setOrderItemId($item->getItem()->getOrderItemId())
                    ->setOrderBoxId($orderBoxModel->getItemId())
                    ->setOrderId($order->getId())
                    ->setSku($orderItem->getSku())
                    ->setNotPacked(0);
                $this->packedOrderItemRepository->save($packedItem);
            }
        }

        foreach ($this->packer->getUnpackedItems() as $item) {
            $qtyIncrement = 0;
            while ($qtyIncrement < $item->getQtyOrdered()) {
                $orderItemBoxModel = $this->packedOrderItemRepository->create();
                $itemModel = $this->orderItemRepository->get($item->getItemId());
                $orderItemBoxModel->setOrderItemId($itemModel->getItemId())
                    ->setOrderId($itemModel->getOrderId())
                    ->setSku($itemModel->getSku())
                    ->setNotPacked(true)
                    ->setErrorMessage(self::PACKAGE_ERROR_MESSAGE);
                $this->packedOrderItemRepository->save($orderItemBoxModel);
                $qtyIncrement++;
            }
        }

        $order->setWeight(array_sum($this->packer->getWeights()))->save();
    }
}
