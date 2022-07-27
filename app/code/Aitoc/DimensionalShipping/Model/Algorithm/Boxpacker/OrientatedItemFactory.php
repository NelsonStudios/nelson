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

use Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\Psr\Log\LoggerAwareInterface;
use Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\Psr\Log\LoggerAwareTrait;
use Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\Psr\Log\NullLogger;

/**
 * Figure out orientations for an item and a given set of dimensions.
 *
 * @author Doug Wright
 * @internal
 */
class OrientatedItemFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var BoxInterface */
    protected $box;

    /**
     * @var OrientatedItem[]
     */
    protected static $emptyBoxCache = [];

    public function __construct(BoxInterface $box)
    {
        $this->box = $box;
        $this->logger = new NullLogger();
    }

    /**
     * Get the best orientation for an item.
     *
     * @param ItemInterface                $item
     * @param OrientatedItem|null $prevItem
     * @param ItemList            $nextItems
     * @param bool                $isLastItem
     * @param int                 $widthLeft
     * @param int                 $lengthLeft
     * @param int                 $depthLeft
     * @param int                 $rowLength
     * @param int                 $x
     * @param int                 $y
     * @param int                 $z
     * @param PackedItemList      $prevPackedItemList
     *
     * @return OrientatedItem|null
     */
    public function getBestOrientation(
        ItemInterface $item,
        $prevItem,
        ItemList $nextItems,
        $isLastItem,
        $widthLeft,
        $lengthLeft,
        $depthLeft,
        $rowLength,
        $x,
        $y,
        $z,
        PackedItemList $prevPackedItemList
    ) {
        $possibleOrientations = $this->getPossibleOrientations($item, $prevItem, $widthLeft, $lengthLeft, $depthLeft, $x, $y, $z, $prevPackedItemList);
        $usableOrientations = $this->getUsableOrientations($item, $possibleOrientations, $isLastItem);

        if (empty($usableOrientations)) {
            return null;
        }

        usort($usableOrientations, function (OrientatedItem $a, OrientatedItem $b) use ($widthLeft, $lengthLeft, $depthLeft, $nextItems, $rowLength, $x, $y, $z, $prevPackedItemList) {
            $orientationAWidthLeft = $widthLeft - $a->getWidth();
            $orientationALengthLeft = $lengthLeft - $a->getLength();
            $orientationADepthLeft = $depthLeft - $a->getDepth();
            $orientationBWidthLeft = $widthLeft - $b->getWidth();
            $orientationBLengthLeft = $lengthLeft - $b->getLength();
            $orientationBDepthLeft = $depthLeft - $b->getDepth();

            $orientationAMinGap = min($orientationAWidthLeft, $orientationALengthLeft);
            $orientationBMinGap = min($orientationBWidthLeft, $orientationBLengthLeft);

            if ($orientationAMinGap === 0) { // prefer A if it leaves no gap
                return -1;
            }
            if ($orientationBMinGap === 0) { // prefer B if it leaves no gap
                return 1;
            }

            // prefer leaving room for next item in current row
            if ($nextItems->count()) {
                $nextItemFitA = count($this->getPossibleOrientations($nextItems->top(), $a, $orientationAWidthLeft, $lengthLeft, $depthLeft, $x, $y, $z, $prevPackedItemList));
                $nextItemFitB = count($this->getPossibleOrientations($nextItems->top(), $b, $orientationBWidthLeft, $lengthLeft, $depthLeft, $x, $y, $z, $prevPackedItemList));
                if ($nextItemFitA && !$nextItemFitB) {
                    return -1;
                }
                if ($nextItemFitB && !$nextItemFitA) {
                    return 1;
                }

                // if not an easy either/or, do a partial lookahead
                $additionalPackedA = $this->calculateAdditionalItemsPackedWithThisOrientation($a, $nextItems, $widthLeft, $lengthLeft, $depthLeft, $rowLength, $x, $y, $z, $prevPackedItemList);
                $additionalPackedB = $this->calculateAdditionalItemsPackedWithThisOrientation($b, $nextItems, $widthLeft, $lengthLeft, $depthLeft, $rowLength, $x, $y, $z, $prevPackedItemList);
                if ($additionalPackedA !== $additionalPackedB) {
                    return $additionalPackedB <=> $additionalPackedA;
                }
            }
            // otherwise prefer leaving minimum possible gap, or the greatest footprint
            return $orientationADepthLeft <=> $orientationBDepthLeft ?: $orientationAMinGap <=> $orientationBMinGap ?: $a->getSurfaceFootprint() <=> $b->getSurfaceFootprint();
        });

        $bestFit = reset($usableOrientations);
        $this->logger->debug('Selected best fit orientation', ['orientation' => $bestFit]);

        return $bestFit;
    }

    /**
     * Find all possible orientations for an item.
     *
     * @param ItemInterface                $item
     * @param OrientatedItem|null $prevItem
     * @param int                 $widthLeft
     * @param int                 $lengthLeft
     * @param int                 $depthLeft
     * @param int                 $x
     * @param int                 $y
     * @param int                 $z
     * @param PackedItemList      $prevPackedItemList
     *
     * @return OrientatedItem[]
     */
    public function getPossibleOrientations(
        ItemInterface $item,
        $prevItem,
        $widthLeft,
        $lengthLeft,
        $depthLeft,
        $x,
        $y,
        $z,
        PackedItemList $prevPackedItemList
    ) {
        $orientations = $orientationsDimensions = [];

        $isSame = false;
        if ($prevItem) {
            $itemADimensions = [$item->getWidth(), $item->getLength(), $item->getDepth()];
            $itemBDimensions = [$prevItem->getWidth(), $prevItem->getLength(), $prevItem->getDepth()];
            sort($itemADimensions);
            sort($itemBDimensions);
            $isSame = ($itemADimensions === $itemBDimensions);
        }

        //Special case items that are the same as what we just packed - keep orientation
        if ($isSame && $prevItem) {
            $orientationsDimensions[] = [$prevItem->getWidth(), $prevItem->getLength(), $prevItem->getDepth()];
        } else {
            //simple 2D rotation
            $orientationsDimensions[] = [$item->getWidth(), $item->getLength(), $item->getDepth()];
            $orientationsDimensions[] = [$item->getLength(), $item->getWidth(), $item->getDepth()];

            //add 3D rotation if we're allowed
            if (!$item->getKeepFlat()) {
                $orientationsDimensions[] = [$item->getWidth(), $item->getDepth(), $item->getLength()];
                $orientationsDimensions[] = [$item->getLength(), $item->getDepth(), $item->getWidth()];
                $orientationsDimensions[] = [$item->getDepth(), $item->getWidth(), $item->getLength()];
                $orientationsDimensions[] = [$item->getDepth(), $item->getLength(), $item->getWidth()];
            }
        }

        //remove any that simply don't fit
        $orientationsDimensions = array_unique($orientationsDimensions, SORT_REGULAR);
        $orientationsDimensions = array_filter($orientationsDimensions, static function (array $i) use ($widthLeft, $lengthLeft, $depthLeft) {
            return $i[0] <= $widthLeft && $i[1] <= $lengthLeft && $i[2] <= $depthLeft;
        });

        foreach ($orientationsDimensions as $dimensions) {
            $orientations[] = new OrientatedItem($item, $dimensions[0], $dimensions[1], $dimensions[2]);
        }

        if ($item instanceof ConstrainedPlacementItem) {
            $box = $this->box;
            $orientations = array_filter($orientations, static function (OrientatedItem $i) use ($box, $x, $y, $z, $prevPackedItemList) {
                /** @var ConstrainedPlacementItem $constrainedItem */
                $constrainedItem = $i->getItem();

                return $constrainedItem->canBePacked($box, $prevPackedItemList, $x, $y, $z, $i->getWidth(), $i->getLength(), $i->getDepth());
            });
        }

        return $orientations;
    }

    /**
     * @param  ItemInterface             $item
     * @return OrientatedItem[]
     */
    public function getPossibleOrientationsInEmptyBox(ItemInterface $item)
    {
        $cacheKey = $item->getWidth() .
            '|' .
            $item->getLength() .
            '|' .
            $item->getDepth() .
            '|' .
            ($item->getKeepFlat() ? '2D' : '3D') .
            '|' .
            $this->box->getInnerWidth() .
            '|' .
            $this->box->getInnerLength() .
            '|' .
            $this->box->getInnerDepth();

        if (isset(static::$emptyBoxCache[$cacheKey])) {
            $orientations = static::$emptyBoxCache[$cacheKey];
        } else {
            $orientations = $this->getPossibleOrientations(
                $item,
                null,
                $this->box->getInnerWidth(),
                $this->box->getInnerLength(),
                $this->box->getInnerDepth(),
                0,
                0,
                0,
                new PackedItemList()
            );
            static::$emptyBoxCache[$cacheKey] = $orientations;
        }

        return $orientations;
    }

    /**
     * @param ItemInterface             $item
     * @param OrientatedItem[] $possibleOrientations
     * @param bool             $isLastItem
     *
     * @return OrientatedItem[]
     */
    protected function getUsableOrientations(
        ItemInterface $item,
        $possibleOrientations,
        $isLastItem
    ) {
        $orientationsToUse = $stableOrientations = $unstableOrientations = [];

        // Divide possible orientations into stable (low centre of gravity) and unstable (high centre of gravity)
        foreach ($possibleOrientations as $orientation) {
            if ($orientation->isStable()) {
                $stableOrientations[] = $orientation;
            } else {
                $unstableOrientations[] = $orientation;
            }
        }

        /*
         * We prefer to use stable orientations only, but allow unstable ones if either
         * the item is the last one left to pack OR
         * the item doesn't fit in the box any other way
         */
        if (count($stableOrientations) > 0) {
            $orientationsToUse = $stableOrientations;
        } elseif (count($unstableOrientations) > 0) {
            $stableOrientationsInEmptyBox = $this->getStableOrientationsInEmptyBox($item);

            if ($isLastItem || count($stableOrientationsInEmptyBox) === 0) {
                $orientationsToUse = $unstableOrientations;
            }
        }

        return $orientationsToUse;
    }

    /**
     * Return the orientations for this item if it were to be placed into the box with nothing else.
     *
     * @param  ItemInterface  $item
     * @return array
     */
    protected function getStableOrientationsInEmptyBox(ItemInterface $item)
    {
        $orientationsInEmptyBox = $this->getPossibleOrientationsInEmptyBox($item);

        return array_filter(
            $orientationsInEmptyBox,
            function (OrientatedItem $orientation) {
                return $orientation->isStable();
            }
        );
    }

    /**
     * Compare two items to see if they have same dimensions.
     *
     * @param ItemInterface $itemA
     * @param ItemInterface $itemB
     *
     * @return bool
     */
    public function isSameDimensions(ItemInterface $itemA, ItemInterface $itemB)
    {
        $itemADimensions = [$itemA->getWidth(), $itemA->getLength(), $itemA->getDepth()];
        $itemBDimensions = [$itemB->getWidth(), $itemB->getLength(), $itemB->getDepth()];
        sort($itemADimensions);
        sort($itemBDimensions);

        return $itemADimensions === $itemBDimensions;
    }

    /**
     * Approximation of a forward-looking packing.
     *
     * Not an actual packing, that has additional logic regarding constraints and stackability, this focuses
     * purely on fit.
     *
     * @param  OrientatedItem $prevItem
     * @param  ItemList       $nextItems
     * @param  int            $originalWidthLeft
     * @param  int            $originalLengthLeft
     * @param  int            $depthLeft
     * @param  int            $currentRowLengthBeforePacking
     * @param int             $x
     * @param int             $y
     * @param int             $z
     * @param PackedItemList  $prevPackedItemList
     * @return int
     */
    protected function calculateAdditionalItemsPackedWithThisOrientation(
        OrientatedItem $prevItem,
        ItemList $nextItems,
        $originalWidthLeft,
        $originalLengthLeft,
        $depthLeft,
        $currentRowLengthBeforePacking,
        $x,
        $y,
        $z,
        PackedItemList $prevPackedItemList = null
    ) {
        $packedCount = 0;

        // first try packing into current row
        $count = max(1 , 8 - floor(log($nextItems->count(), 2)));
        $currentRowWorkingSetItems = $nextItems->topN($count); // cap lookahead as this gets recursive and slow
        $nextRowWorkingSetItems = new ItemList();
        $widthLeft = $originalWidthLeft - $prevItem->getWidth();
        $lengthLeft = $originalLengthLeft;
        while (count($currentRowWorkingSetItems) > 0 && $widthLeft > 0) {
            $itemToPack = $currentRowWorkingSetItems->extract();
            $orientatedItem = $this->getBestOrientation($itemToPack, $prevItem, $currentRowWorkingSetItems, !count($currentRowWorkingSetItems), $widthLeft, $lengthLeft, $depthLeft, $currentRowLengthBeforePacking, $x, $y, $z ,$prevPackedItemList);
            if ($orientatedItem instanceof OrientatedItem) {
                ++$packedCount;
                $widthLeft -= $orientatedItem->getWidth();
                $prevItem = $orientatedItem;
            } else {
                $nextRowWorkingSetItems->insert($itemToPack);
            }
        }

        // then see what happens if we try in the next row
        $widthLeft = $originalWidthLeft;
        $lengthLeft = $originalLengthLeft - $prevItem->getLength();
        while (count($nextRowWorkingSetItems) > 0 && $widthLeft > 0) {
            $itemToPack = $nextRowWorkingSetItems->extract();
            $orientatedItem = $this->getBestOrientation($itemToPack, $prevItem, $nextRowWorkingSetItems, !count($nextRowWorkingSetItems), $widthLeft, $lengthLeft, $depthLeft, $currentRowLengthBeforePacking, $x, $y, $z ,$prevPackedItemList);
            if ($orientatedItem instanceof OrientatedItem) {
                ++$packedCount;
                $widthLeft -= $orientatedItem->getWidth();
                $prevItem = $orientatedItem;
            }
        }

        return $packedCount; // this isn't scientific, but is a reasonable proxy for success from an actual forward packing
    }
}