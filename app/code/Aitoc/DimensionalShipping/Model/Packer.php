<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Model;

use Aitoc\DimensionalShipping\Helper\Data;
use Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker;
use Aitoc\DimensionalShipping\Model\BoxRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Aitoc\DimensionalShipping\Model\ResourceModel\Box\CollectionFactory as BoxCollectionFactory;
use Aitoc\DimensionalShipping\Model\ResourceModel\ProductOptions\CollectionFactory as ProductOptionsCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;


class Packer
{
    /**
     * @var Boxpacker\InfalliblePackerFactory
     */
    private $packingModelFactory;

    /**
     * @var Boxpacker\InfalliblePacker
     */
    private $generalPackingModel;
    /**
     * @var Boxpacker\TestBoxFactory
     */
    private $boxModelFactory;

    /**
     * @var BoxCollectionFactory
     */
    private $boxCollection;

    /**
     * @var Boxpacker\TestItemFactory
     */
    private $packingItemModelFactory;

    /**
     * @var ProductOptionsCollectionFactory
     */
    private $productOptionsCollectionFactory;

    /**
     * @var \Aitoc\DimensionalShipping\Model\BoxRepository
     */
    private $boxRepository;

    /**
     * @var OrderItemCollectionFactory
     */
    private $orderItemCollectionFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var array
     */
    private $parentIdsOfIgnoredChildren;

    /**
     * @var array
     */
    private $boxes;

    /**
     * @var array
     */
    private $unpackedItems = [];

    /**
     * @var array
     */
    private $quoteItems;

    public function __construct(
        Boxpacker\InfalliblePackerFactory $packingModelFactory,
        Boxpacker\TestBoxFactory $boxModelFactory,
        BoxCollectionFactory $boxCollection,
        Boxpacker\TestItemFactory $packingItemModelFactory,
        ProductOptionsCollectionFactory $productOptionsCollectionFactory,
        BoxRepository $boxRepository,
        OrderItemCollectionFactory $orderItemCollectionFactory,
        Data $helper
    ) {
        $this->packingModelFactory = $packingModelFactory;
        $this->generalPackingModel = $packingModelFactory->create();
        $this->boxModelFactory = $boxModelFactory;
        $this->boxCollection = $boxCollection;
        $this->packingItemModelFactory = $packingItemModelFactory;
        $this->productOptionsCollectionFactory = $productOptionsCollectionFactory;
        $this->boxRepository = $boxRepository;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->helper = $helper;
        $this->parentIdsOfIgnoredChildren = [];
        $this->fillPackingModelWithBoxes($this->generalPackingModel);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getUnpackedItems()
    {
        if ($this->boxes === null) {
            throw new LocalizedException(__('Boxes have not been packed yet.'));
        }
        return $this->unpackedItems;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getBoxes()
    {
        if ($this->boxes === null) {
            throw new LocalizedException(__('Boxes have not been packed yet.'));
        }

        return $this->boxes;
    }

    /**
     * @param \Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\PackedBox $packedBox
     * @return array
     */
    public function getCmDimensionsByBox($packedBox)
    {
        return [
            'height' => (float)$packedBox->getBox()->getOuterDepth() / 10,
            'length' => (float)$packedBox->getBox()->getOuterLength() / 10,
            'width' => (float)$packedBox->getBox()->getOuterWidth() / 10
        ];
    }


    /**
     * @param bool $useBillable
     * @return array
     * @throws LocalizedException
     */
    public function getWeights($useBillable = false)
    {
        if ($this->boxes === null) {
            throw new LocalizedException(__('Boxes have not been packed yet.'));
        }

        $weights = [];
        $unpackedWeight = 0;
        $useBillable = $useBillable && $this->helper->getConfigValue('DimensionalShipping/shipping_rates/billable_weight');
        foreach ($this->boxes as $packedBox) {
            $weights[] = $useBillable ? $this->getBillWeightByPackedBox($packedBox) : $packedBox->getWeight();
        }

        foreach ($this->getUnpackedItems() as $item) {
            $unpackedWeight += $item->getWeight() * $this->getItemQty($item);
        }
        if ($unpackedWeight) {
            $weights[] = $unpackedWeight;
        }

        return $weights;
    }


    /**
     * @param \Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\PackedBox $packedBox
     * @return float|int
     */
    private function getBillWeightByPackedBox($packedBox)
    {
        //if multiplicator is default(0), 5000 is used of cm/kg and 139 for in/lbs. packed box uses mm.
        $divisor = (int)$this->helper->getConfigValue('DimensionalShipping/shipping_rates/dim_weight_divisor');
        if ('lbs' === $this->helper->getConfigValue('general/locale/weight_unit')) {
            $divisor = ($divisor ?: 139) * 16387.064; //25.4^3 (mm/inc)
        } else {
            $divisor = ($divisor ?: 5000) * 1000; //10^3 (mm/cm)
        }
        $volWeight = (float)$packedBox->getBox()->getOuterDepth()
            * (float)$packedBox->getBox()->getOuterLength()
            * (float)$packedBox->getBox()->getOuterWidth()
            / $divisor;
        $weight = $packedBox->getWeight();
        $billWeight = $volWeight > $weight ? $volWeight : $weight;
        return $billWeight;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[]|\Magento\Sales\Model\Order\Item[] $items
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($items = null)
    {
        if ($this->boxes !== null && !empty($items[0]) && $items[0] instanceof \Magento\Quote\Model\Quote\Item) {
            return $this->boxes;
        }

        if ($items === null) {
            if ($this->boxes !== null) {
                return $this->boxes;
            }
            throw new LocalizedException(__('Items are required to pack order.'));
        }

        $this->unpackedItems = [];
        $this->boxes = [];
        if (!$this->boxCollection->create()->getSize()) {
            $this->unpackedItems = $items;
            return $this->boxes;
        }

        $this->quoteItems = $items;
        foreach ($items as $item) {
            //Check type
            if (!$this->helper->checkProductsType($item)) {
                continue;
            }
            // Check if current item is a part of composite product, already packed on its level (composite parent iterate first)
            if ($item->getParentItemId() && in_array($item->getParentItemId(), $this->parentIdsOfIgnoredChildren)) {
                continue;
            }
            $productOptions = $this->getProductOptionsByProductId($item->getProductId());
            // process composite
            if (in_array($item->getProductType(), ['bundle', 'configurable'])
                && $this->processCompositeProducts($item, $productOptions) === false
            ) {
                continue;
            }
            //check weight and options
            if (!$this->validateProductOptions($productOptions) || !$item->getWeight()) {
                $this->unpackedItems[$item->getItemId()] = $item;
                continue;
            }

            $this->helper->convertUnits($productOptions, 'item');
            if ($productOptions->getSpecialBox() || $productOptions->getPackSeparately()) {
                $this->packSeparately($item, $productOptions);
            } else {
                $this->addToPackingModel($this->generalPackingModel, $item, $productOptions);
            }
        }

        $this->pack();
        return $this->boxes;
    }


    /**
     * @param $packingModel
     * @param null $boxId
     * @return mixed
     */
    private function fillPackingModelWithBoxes($packingModel, $boxId = null)
    {
        $boxes = $this->boxCollection->create();
        if ($boxId) {
            $boxes->addFieldToFilter('item_id', $boxId);
        }
        foreach ($boxes as $box) {
            $convertedBox = $this->helper->convertUnits($box, 'box');
            $boxModel = $this->boxModelFactory->create([
                'reference' => $convertedBox->getName(),
                'outerWidth' => $convertedBox->getOuterWidth(),
                'outerLength' => $convertedBox->getOuterLength(),
                'outerDepth' => $convertedBox->getOuterHeight(),
                'emptyWeight' => $convertedBox->getEmptyWeight(),
                'innerWidth' => $convertedBox->getWidth(),
                'innerLength' => $convertedBox->getLength(),
                'innerDepth' => $convertedBox->getHeight(),
                'maxWeight' => $convertedBox->getWeight(),
                'boxId' => $convertedBox->getId()
            ]);
            $packingModel->addBox($boxModel);
        }

        return $packingModel;
    }

    /**
     * @param $model
     */
    private function pack($model = null)
    {
        if (!$model) {
            $model = $this->generalPackingModel;
        }
        if (!$this->helper->getGeneralConfig('redistribution')) {
            $model->setMaxBoxesToBalanceWeight(0);
        }
        foreach ($model->pack() as $packedBox) {
            $this->boxes[] = $packedBox;
        }
        foreach ($model->getUnpackedItems() as $item) {
            /** @var \Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\TestItem item */
            foreach ($this->quoteItems as $quoteItem) {
                if ($quoteItem->getItemId() == $item->getOrderItemId()) {
                    $this->unpackedItems[$quoteItem->getItemId()] = $quoteItem;
                    break;
                }
            }
        }
    }

    /**
     * @param $item
     * @param $productOptions
     */
    private function packSeparately($item, $productOptions)
    {
        $specialBoxId = $productOptions->getSpecialBox() && $productOptions->getSelectBox()
            ? $productOptions->getSelectBox() : null;
        $isSeparateItem = $productOptions->getPackSeparately() == Data::PACK_SEPARATELY_ITEM;
        for ($i = 0; $i < ($isSeparateItem ? $this->getItemQty($item) : 1); ++$i) {
            $packingModel = $this->packingModelFactory->create();
            $this->fillPackingModelWithBoxes($packingModel, $specialBoxId);
            $this->addToPackingModel($packingModel, $item, $productOptions, ($isSeparateItem ? 1 : $this->getItemQty($item)));
            $this->pack($packingModel);
        }
    }

    /**
     * @param $productId
     * @return mixed
     */
    private function getProductOptionsByProductId($productId)
    {
        $optionsCollection = $this->productOptionsCollectionFactory->create();
        return $optionsCollection->addFieldToFilter('product_id', $productId)->getFirstItem();
    }

    /**
     * @param $productOptions
     * @return bool
     */
    private function validateProductOptions($productOptions)
    {
        $productDimensionalFields = $this->helper->getProductOptionsModelFields('long');
        foreach ($productDimensionalFields as $field) {
            $data = $productOptions->getData($field);
            if ($data <= 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $packingModel
     * @param $item
     * @param $options
     * @param null $qty
     */
    private function addToPackingModel($packingModel, $item, $options, $qty = null)
    {
        $itemModel = $this->packingItemModelFactory->create([
                'description' => $item->getName(),
                'width'       => $options->getWidth(),
                'length'      => $options->getLength(),
                'depth'       => $options->getHeight(),
                'weight'      => $item->getProduct()->getWeight(),
                'keepFlat'    => 0,
                'orderItemId' => $item->getItemId()
        ]);
        $packingModel->addItem($itemModel, $qty ?: $this->getItemQty($item));
    }

    /**
     * @param $item
     * @param $productOptions
     * @return bool
     */
    private function processCompositeProducts($item, $productOptions)
    {
        if ($item->getProductType() === 'configurable'
            || ($item->getProductType() === 'bundle' && $item->getProduct()->getWeightType() == 1)) {
            //configurable or bundle with static weight - try to pack parent
            if ($item->getWeight() && $this->validateProductOptions($productOptions)) {
                // exclude child items from packing
                $this->parentIdsOfIgnoredChildren[] = $item->getItemId();
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item|\Magento\Sales\Model\Order\Item $item
     * @return int
     */
    private function getItemQty($item)
    {
        return $item->getQty() !== null ? $item->getQty() : $item->getQtyOrdered();
    }
}