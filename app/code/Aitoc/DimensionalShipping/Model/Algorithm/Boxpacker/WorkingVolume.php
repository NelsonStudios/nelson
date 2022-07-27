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
 * Class WorkingVolume.
 * @internal
 */
class WorkingVolume implements BoxInterface, \JsonSerializable
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var int
     */
    private $maxWeight;

    /**
     * Constructor.
     *
     * @param int $width
     * @param int $length
     * @param int $depth
     * @param int $maxWeight
     */
    public function __construct(
        $width,
        $length,
        $depth,
        $maxWeight
    ) {
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->maxWeight = $maxWeight;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return 'Working Volume';
    }

    /**
     * @return int
     */
    public function getOuterWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getOuterLength()
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getOuterDepth()
    {
        return $this->depth;
    }

    /**
     * @return int
     */
    public function getEmptyWeight()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getInnerWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getInnerLength()
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getInnerDepth()
    {
        return $this->depth;
    }

    /**
     * @return int
     */
    public function getMaxWeight()
    {
        return $this->maxWeight;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'reference' => $this->getReference(),
            'width' => $this->width,
            'length' => $this->length,
            'depth' => $this->depth,
            'maxWeight' => $this->maxWeight,
        ];
    }
}
