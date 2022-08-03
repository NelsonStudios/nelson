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

class TestBox implements BoxInterface, \JsonSerializable
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @var int
     */
    private $outerWidth;

    /**
     * @var int
     */
    private $outerLength;

    /**
     * @var int
     */
    private $outerDepth;

    /**
     * @var int
     */
    private $emptyWeight;

    /**
     * @var int
     */
    private $innerWidth;

    /**
     * @var int
     */
    private $innerLength;

    /**
     * @var int
     */
    private $innerDepth;

    /**
     * @var int
     */
    private $maxWeight;

    /**
     * @var int
     */
    private $boxId;

    public function __construct(
        $reference,
        $outerWidth,
        $outerLength,
        $outerDepth,
        $emptyWeight,
        $innerWidth,
        $innerLength,
        $innerDepth,
        $maxWeight,
        $boxId
    ) {
        $this->reference = $reference;
        $this->outerWidth = $outerWidth;
        $this->outerLength = $outerLength;
        $this->outerDepth = $outerDepth;
        $this->emptyWeight = $emptyWeight;
        $this->innerWidth = $innerWidth;
        $this->innerLength = $innerLength;
        $this->innerDepth = $innerDepth;
        $this->maxWeight = $maxWeight;
        $this->boxId = $boxId;
    }

    /**
     * @return int
     */
    public function getBoxId()
    {
        return $this->boxId;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return int
     */
    public function getOuterWidth()
    {
        return $this->outerWidth;
    }

    /**
     * @return int
     */
    public function setOuterWidth($width)
    {
        $this->outerWidth = $width;
    }

    /**
     * @return int
     */
    public function getOuterLength()
    {
        return $this->outerLength;
    }

    /**
     * @return int
     */
    public function setOuterLength($length)
    {
        $this->outerLength = $length;
    }

    /**
     * @return int
     */
    public function getOuterDepth()
    {
        return $this->outerDepth;
    }

    /**
     * @return int
     */
    public function setOuterDepth($depth)
    {
        $this->outerDepth = $depth;
    }

    /**
     * @return int
     */
    public function getEmptyWeight()
    {
        return $this->emptyWeight;
    }

    /**
     * @return int
     */
    public function setEmptyWeight($weight)
    {
        $this->emptyWeight = $weight;
    }

    /**
     * @return int
     */
    public function getInnerWidth()
    {
        return $this->innerWidth;
    }

    /**
     * @return int
     */
    public function setInnerWidth($width)
    {
        $this->innerWidth = $width;
    }

    /**
     * @return int
     */
    public function getInnerLength()
    {
        return $this->innerLength;
    }

    /**
     * @param $length
     */
    public function setInnerLength($length)
    {
        $this->innerLength = $length;
    }

    /**
     * @return int
     */
    public function getInnerDepth()
    {
        return $this->innerDepth;
    }

    /**
     * @param $depth
     */
    public function setInnerDepth($depth)
    {
        $this->innerDepth = $depth;
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
            'reference' => $this->reference,
            'innerWidth' => $this->innerWidth,
            'innerLength' => $this->innerLength,
            'innerDepth' => $this->innerDepth,
            'emptyWeight' => $this->emptyWeight,
            'maxWeight' => $this->maxWeight,
        ];
    }
}
