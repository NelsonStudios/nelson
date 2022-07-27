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
 * List of boxes available to put items into, ordered by volume.
 *
 * @author Doug Wright
 */
class BoxList implements \IteratorAggregate
{
    /**
     * List containing boxes.
     *
     * @var BoxInterface[]
     */
    private $list = [];

    /**
     * Has this list already been sorted?
     *
     * @var bool
     */
    private $isSorted = false;

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
     * @param BoxInterface $item
     */
    public function insert(BoxInterface $item)
    {
        $this->list[] = $item;
    }

    /**
     * @param BoxInterface $boxA
     * @param BoxInterface $boxB
     *
     * @return int
     */
    public function compare($boxA, $boxB)
    {
        $boxAVolume = $boxA->getInnerWidth() * $boxA->getInnerLength() * $boxA->getInnerDepth();
        $boxBVolume = $boxB->getInnerWidth() * $boxB->getInnerLength() * $boxB->getInnerDepth();

        $volumeDecider = $boxAVolume <=> $boxBVolume; // try smallest box first
        $emptyWeightDecider = $boxB->getEmptyWeight() <=> $boxA->getEmptyWeight(); // with smallest empty weight

        if ($volumeDecider !== 0) {
            return $volumeDecider;
        }
        if ($emptyWeightDecider !== 0) {
            return $emptyWeightDecider;
        }

        // maximum weight capacity as fallback decider
        return ($boxA->getMaxWeight() - $boxA->getEmptyWeight()) <=> ($boxB->getMaxWeight() - $boxB->getEmptyWeight());
    }
}
