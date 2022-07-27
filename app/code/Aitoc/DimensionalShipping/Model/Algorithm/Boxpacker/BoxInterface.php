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
 * A "box" (or envelope?) to pack items into.
 *
 * @author Doug Wright
 */
interface BoxInterface
{
    /**
     * Reference for box type (e.g. SKU or description).
     *
     * @return string
     */
    public function getReference();

    /**
     * Outer width in mm.
     *
     * @return int
     */
    public function getOuterWidth();

    /**
     * Outer length in mm.
     *
     * @return int
     */
    public function getOuterLength();

    /**
     * Outer depth in mm.
     *
     * @return int
     */
    public function getOuterDepth();

    /**
     * Empty weight in g.
     *
     * @return int
     */
    public function getEmptyWeight();

    /**
     * Inner width in mm.
     *
     * @return int
     */
    public function getInnerWidth();

    /**
     * Inner length in mm.
     *
     * @return int
     */
    public function getInnerLength();

    /**
     * Inner depth in mm.
     *
     * @return int
     */
    public function getInnerDepth();

    /**
     * Max weight the packaging can hold in g.
     *
     * @return int
     */
    public function getMaxWeight();
}
