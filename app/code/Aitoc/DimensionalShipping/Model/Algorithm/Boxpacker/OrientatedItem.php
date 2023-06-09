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
class OrientatedItem implements \JsonSerializable
{
    /**
     * @var ItemInterface
     */
    protected $item;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var int
     */
    protected $depth;

    /**
     * @var int
     */
    protected $surfaceFootprint;

    /**
     * @var bool[]
     */
    protected static $stabilityCache = [];

    /**
     * Constructor.
     *
     * @param ItemInterface $item
     * @param int  $width
     * @param int  $length
     * @param int  $depth
     */
    public function __construct(ItemInterface $item, $width, $length, $depth)
    {
        $this->item = $item;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->surfaceFootprint = $width * $length;
    }

    /**
     * Item.
     *
     * @return ItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Item width in mm in it's packed orientation.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Item length in mm in it's packed orientation.
     *
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Item depth in mm in it's packed orientation.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Calculate the surface footprint of the current orientation.
     *
     * @return int
     */
    public function getSurfaceFootprint()
    {
        return $this->surfaceFootprint;
    }

    /**
     * Is this item stable (low centre of gravity), calculated as if the tipping point is >15 degrees.
     *
     * N.B. Assumes equal weight distribution.
     *
     * @return bool
     */
    public function isStable()
    {
        $cacheKey = $this->width . '|' . $this->length . '|' . $this->depth;

        return static::$stabilityCache[$cacheKey] ?? (static::$stabilityCache[$cacheKey] = atan(min($this->length, $this->width) / ($this->depth ?: 1)) > 0.261);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'item' => $this->item,
            'width' => $this->width,
            'length' => $this->length,
            'depth' => $this->depth,
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->width . '|' . $this->length . '|' . $this->depth;
    }
}
