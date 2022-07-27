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
 * An item to be packed where additional constraints need to be considered. Only implement this interface if you actually
 * need this additional functionality as it will slow down the packing algorithm.
 *
 * @author Doug Wright
 */
interface ConstrainedPlacementItem extends ItemInterface
{
    /**
     * Hook for user implementation of item-specific constraints, e.g. max <x> batteries per box.
     *
     * @param  BoxInterface            $box
     * @param  PackedItemList $alreadyPackedItems
     * @param  int            $proposedX
     * @param  int            $proposedY
     * @param  int            $proposedZ
     * @param  int            $width
     * @param  int            $length
     * @param  int            $depth
     * @return bool
     */
    public function canBePacked(
        BoxInterface $box,
        PackedItemList $alreadyPackedItems,
        $proposedX,
        $proposedY,
        $proposedZ,
        $width,
        $length,
        $depth
    );
}
