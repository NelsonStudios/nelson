<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */

/**
 * Box packing (3D bin packing, knapsack problem).
 *
 * @author Doug Wright
 */

namespace Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker;

/**
 * A "box" with items.
 *
 * @author Doug Wright
 */
class PackedBox
{
    /**
     * Box used.
     *
     * @var BoxInterface
     */
    protected $box;

    /**
     * Items in the box.
     *
     * @var PackedItemList
     */
    protected $items;

    /**
     * Total weight of items in the box.
     *
     * @var int
     */
    protected $itemWeight;

    /**
     * Get box used.
     *
     * @return BoxInterface
     */
    public function getBox()
    {
        return $this->box;
    }

    /**
     * Get items packed.
     *
     * @return PackedItemList
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get packed weight.
     *
     * @return int weight in grams
     */
    public function getWeight()
    {
        return $this->box->getEmptyWeight() + $this->getItemWeight();
    }

    /**
     * Get packed weight of the items only.
     *
     * @return int weight in grams
     */
    public function getItemWeight()
    {
        if ($this->itemWeight !== null) {
            return $this->itemWeight;
        }

        $this->itemWeight = 0;
        /** @var PackedItem $item */
        foreach ($this->items as $item) {
            $this->itemWeight += $item->getItem()->getWeight();
        }

        return $this->itemWeight;
    }

    /**
     * Get remaining width inside box for another item.
     *
     * @return int
     */
    public function getRemainingWidth()
    {
        return $this->box->getInnerWidth() - $this->getUsedWidth();
    }

    /**
     * Get remaining length inside box for another item.
     *
     * @return int
     */
    public function getRemainingLength()
    {
        return $this->box->getInnerLength() - $this->getUsedLength();
    }

    /**
     * Get remaining depth inside box for another item.
     *
     * @return int
     */
    public function getRemainingDepth()
    {
        return $this->box->getInnerDepth() - $this->getUsedDepth();
    }

    /**
     * Used width inside box for packing items.
     *
     * @return int
     */
    public function getUsedWidth()
    {
        $maxWidth = 0;

        /** @var PackedItem $item */
        foreach ($this->items as $item) {
            $maxWidth = max($maxWidth, $item->getX() + $item->getWidth());
        }

        return $maxWidth;
    }

    /**
     * Used length inside box for packing items.
     *
     * @return int
     */
    public function getUsedLength()
    {
        $maxLength = 0;

        /** @var PackedItem $item */
        foreach ($this->items as $item) {
            $maxLength = max($maxLength, $item->getY() + $item->getLength());
        }

        return $maxLength;
    }

    /**
     * Used depth inside box for packing items.
     *
     * @return int
     */
    public function getUsedDepth()
    {
        $maxDepth = 0;

        /** @var PackedItem $item */
        foreach ($this->items as $item) {
            $maxDepth = max($maxDepth, $item->getZ() + $item->getDepth());
        }

        return $maxDepth;
    }

    /**
     * Get remaining weight inside box for another item.
     *
     * @return int
     */
    public function getRemainingWeight()
    {
        return $this->box->getMaxWeight() - $this->getWeight();
    }

    /**
     * @return int
     */
    public function getInnerVolume()
    {
        return $this->box->getInnerWidth() * $this->box->getInnerLength() * $this->box->getInnerDepth();
    }

    /**
     * Get used volume of the packed box.
     *
     * @return int
     */
    public function getUsedVolume()
    {
        $volume = 0;

        /** @var PackedItem $item */
        foreach ($this->items as $item) {
            $volume += $item->getVolume();
        }

        return $volume;
    }

    /**
     * Get unused volume of the packed box.
     *
     * @return int
     */
    public function getUnusedVolume()
    {
        return $this->getInnerVolume() - $this->getUsedVolume();
    }

    /**
     * Get volume utilisation of the packed box.
     *
     * @return float
     */
    public function getVolumeUtilisation()
    {
        return round($this->getUsedVolume() / $this->getInnerVolume() * 100, 1);
    }

    /**
     * Constructor.
     *
     * @param BoxInterface            $box
     * @param PackedItemList $packedItemList
     */
    public function __construct(BoxInterface $box, PackedItemList $packedItemList)
    {
        $this->box = $box;
        $this->items = $packedItemList;
    }
}
