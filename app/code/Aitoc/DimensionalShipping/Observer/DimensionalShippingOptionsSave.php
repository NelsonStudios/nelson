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
use Aitoc\DimensionalShipping\Model\ProductOptionsFactory;
use Aitoc\DimensionalShipping\Model\ProductOptionsRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class DimensionalShippingOptionsSave implements ObserverInterface
{
    protected $helper;
    protected $productOptionsModel;
    protected $productOptionsRepository;

    /**
     * DimensionalShippingOptionsSave constructor.
     *
     * @param Data                     $helper
     * @param ProductOptionsRepository $productOptionsRepository
     * @param ProductOptionsFactory    $productOptionsModel
     */
    public function __construct(
        Data $helper,
        ProductOptionsRepository $productOptionsRepository,
        ProductOptionsFactory $productOptionsModel
    ) {
        $this->helper                   = $helper;
        $this->productOptionsRepository = $productOptionsRepository;
        $this->productOptionsModel      = $productOptionsModel;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($observer->getEvent()->getObject()->getEventPrefix() == 'catalog_product') {
            $data = $observer->getEvent()->getObject()->getData();
            $productId = $observer->getEvent()->getObject()->getId();
            $currentItem = $this->productOptionsRepository->getByProductId($productId);
            if (!empty($data['ai_width']) && !empty($data['ai_height']) && !empty($data['ai_length'])) {
                if (!$currentItem) {
                    $currentItem = $this->productOptionsModel->create();
                }
                $currentItem->setData('product_id', $observer->getEvent()->getObject()->getId());
                foreach ($this->helper->getFields() as $field) {
                    if (isset($data[$field])) {
                        $currentItem->setData(substr($field, 3), $data[$field]);
                    }
                    if (isset($data['ai_special_box'])) {
                        if ($field == 'ai_select_box' && $data['ai_special_box'] == 0) {
                            $currentItem->setData(substr($field, 3), null);
                        }
                    }
                }
                $currentItem->setUnit($this->helper->getGeneralConfig('unit'));
                $this->productOptionsRepository->save($currentItem);
            } else {
                $this->productOptionsRepository->deleteByProductId($productId);
            }
        }
    }
}
