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

class TestItem implements ItemInterface, \JsonSerializable
{
    /**
     * @var string
     */
    private $description;

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
    private $weight;

    /**
     * @var int
     */
    private $keepFlat;

    /**
     * Test objects that recurse.
     *
     * @var \stdClass
     */
    private $a;

    /**
     * Test objects that recurse.
     *
     * @var \stdClass
     */
    private $b;

    /**
     * @var int
     */
    private $orderItemId;

    /**
     * TestItem constructor.
     *
     * @param string $description
     * @param int    $width
     * @param int    $length
     * @param int    $depth
     * @param int    $weight
     * @param bool   $keepFlat
     * @param int   $orderItemId
     */
    public function __construct(
        $description,
        $width,
        $length,
        $depth,
        $weight,
        $keepFlat,
        $orderItemId)
    {
        $this->description = $description;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->weight = $weight;
        $this->keepFlat = $keepFlat;
        $this->orderItemId = $orderItemId;

        $this->a = new \stdClass();
        $this->b = new \stdClass();

        $this->a->b = $this->b;
        $this->b->a = $this->a;
    }

    /**
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * @param int $id
     */
    public function setOrderItemId($id)
    {
        $this->orderItemId = $id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return bool
     */
    public function getKeepFlat()
    {
        return $this->keepFlat;
    }

    /**
     * @param $keepFlat
     */
    public function setKeepFlat($keepFlat)
    {
        $this->keepFlat = $keepFlat;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'description' => $this->description,
            'width' => $this->width,
            'length' => $this->length,
            'depth' => $this->depth,
            'weight' => $this->weight,
            'keepFlat' => $this->keepFlat,
        ];
    }
}
