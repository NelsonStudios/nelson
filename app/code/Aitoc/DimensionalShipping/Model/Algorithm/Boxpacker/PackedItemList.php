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
 * List of packed items, ordered by volume.
 *
 * @author Doug Wright
 */
class PackedItemList implements \Countable, \IteratorAggregate
{
    /**
     * List containing items.
     *
     * @var PackedItem[]
     */
    private $list = [];

    /**
     * Has this list already been sorted?
     *
     * @var bool
     */
    private $isSorted = false;

    /**
     * @param PackedItem $item
     */
    public function insert(PackedItem $item)
    {
        $this->list[] = $item;
    }

    /**
     * @return \Traversable
     */
    public function getIterator()
    {
        if (!$this->isSorted) {
            usort($this->list, [$this, 'compare']);
            $this->isSorted = true;
        }

        return new \ArrayIterator($this->list);
    }

    /**
     * Number of items in list.
     *
     * @return int
     */
    public function count()
    {
        return count($this->list);
    }

    /**
     * Get copy of this list as a standard PHP array.
     *
     * @internal
     *
     * @return ItemInterface[]
     */
    public function asItemArray()
    {
        return array_map(function (PackedItem $packedItem) {
            return $packedItem->getItem();
        }, $this->list);
    }

    /**
     * @param PackedItem $itemA
     * @param PackedItem $itemB
     *
     * @return int
     */
    private function compare(PackedItem $itemA, PackedItem $itemB)
    {
        $itemAVolume = $itemA->getItem()->getWidth() * $itemA->getItem()->getLength() * $itemA->getItem()->getDepth();
        $itemBVolume = $itemB->getItem()->getWidth() * $itemB->getItem()->getLength() * $itemB->getItem()->getDepth();

        return ($itemBVolume <=> $itemAVolume) ?: ($itemB->getItem()->getWeight() <=> $itemA->getItem()->getWeight());
    }
}
