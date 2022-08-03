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
 * An item to be packed.
 *
 * @author Doug Wright
 */
interface ItemInterface
{
    /**
     * Item SKU etc.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Item width in mm.
     *
     * @return int
     */
    public function getWidth();

    /**
     * Item length in mm.
     *
     * @return int
     */
    public function getLength();

    /**
     * Item depth in mm.
     *
     * @return int
     */
    public function getDepth();

    /**
     * Item weight in g.
     *
     * @return int
     */
    public function getWeight();

    /**
     * Does this item need to be kept flat / packed "this way up"?
     *
     * @return bool
     */
    public function getKeepFlat();
}
