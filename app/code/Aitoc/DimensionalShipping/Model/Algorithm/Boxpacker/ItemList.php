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
 * List of items to be packed, ordered by volume.
 *
 * @author Doug Wright
 */
class ItemList implements \Countable, \IteratorAggregate
{
    /**
     * List containing items.
     *
     * @var ItemInterface[]
     */
    private $list = [];

    /**
     * Has this list already been sorted?
     *
     * @var bool
     */
    private $isSorted = false;

    /**
     * Do a bulk create.
     *
     * @param  ItemInterface[]   $items
     * @param  bool     $preSorted
     * @return ItemList
     */
    public static function fromArray($items, $preSorted = false)
    {
        $list = new static();
        $list->list = array_reverse($items); // internal sort is largest at the end
        $list->isSorted = $preSorted;

        return $list;
    }

    /**
     * @param ItemInterface $item
     */
    public function insert(ItemInterface $item)
    {
        $this->list[] = $item;
    }

    /**
     * Remove item from list.
     *
     * @param ItemInterface $item
     */
    public function remove(ItemInterface $item)
    {
        foreach ($this->list as $key => $itemToCheck) {
            if ($itemToCheck === $item) {
                unset($this->list[$key]);
                break;
            }
        }
    }

    /**
     * @return ItemInterface
     *@internal
     *
     */
    public function extract()
    {
        if (!$this->isSorted) {
            usort($this->list, [$this, 'compare']);
            $this->isSorted = true;
        }

        return array_pop($this->list);
    }

    /**
     * @return ItemInterface
     *@internal
     *
     */
    public function top()
    {
        if (!$this->isSorted) {
            usort($this->list, [$this, 'compare']);
            $this->isSorted = true;
        }

        if (\PHP_VERSION_ID < 70300) {
            return array_slice($this->list, -1, 1)[0];
        }

        return $this->list[array_key_last($this->list)];
    }

    /**
     * @internal
     *
     * @param  int      $n
     * @return ItemList
     */
    public function topN(int $n)
    {
        if (!$this->isSorted) {
            usort($this->list, [$this, 'compare']);
            $this->isSorted = true;
        }

        $topNList = new self();
        $topNList->list = array_slice($this->list, -$n, $n);
        $topNList->isSorted = true;

        return $topNList;
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

        return new \ArrayIterator(array_reverse($this->list));
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
     * @param ItemInterface $itemA
     * @param ItemInterface $itemB
     *
     * @return int
     */
    private function compare(ItemInterface $itemA, ItemInterface $itemB)
    {
        $volumeDecider = $itemA->getWidth() * $itemA->getLength() * $itemA->getDepth() <=> $itemB->getWidth() * $itemB->getLength() * $itemB->getDepth();
        if ($volumeDecider !== 0) {
            return $volumeDecider;
        }
        $weightDecider = $itemA->getWeight() - $itemB->getWeight();
        if ($weightDecider !== 0) {
            return $weightDecider;
        }

        return $itemB->getDescription() <=> $itemA->getDescription();
    }
}
