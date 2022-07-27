<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


/**
 * Copyright © 2017 Aitoc. All rights reserved.
 */

namespace Aitoc\DimensionalShipping\Api\Data;

interface BoxInterface
{
    /**
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'item_id';
    const NAME = 'name';
    const WEIGHT = 'weight';
    const EMPTY_WEIGHT = 'empty_weight';
    const HEIGHT = 'height';
    const WIDTH = 'width';
    const LENGTH = 'length';
    const OUTER_WIDTH = 'outer_width';
    const OUTER_HEIGHT = 'outer_height';
    const OUTER_LENGTH = 'outer_length';
    const UNIT = 'unit';

    /**
     * Returns entity_id field
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * @param int $itemId
     *
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Returns name field
     *
     * @return int|null
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Returns weight field
     *
     * @return int|null
     */
    public function getWeight();

    /**
     * @param float $weight
     *
     * @return $this
     */
    public function setWeight($weight);

    /**
     * Returns empty_weight field
     *
     * @return int|null
     */
    public function getEmptyWeight();

    /**
     * @param float $weight
     *
     * @return $this
     */
    public function setEmptyWeight($weight);

    /**
     * Returns width field
     *
     * @return int|null
     */
    public function getWidth();

    /**
     * @param float $width
     *
     * @return $this
     */
    public function setWidth($width);

    /**
     * Returns height field
     *
     * @return int|null
     */
    public function getHeight();

    /**
     * @param float $height
     *
     * @return $this
     */
    public function setHeight($height);

    /**
     * Returns length field
     *
     * @return mixed
     */
    public function getLength();

    /**
     * @param $length
     *
     * @return mixed
     */
    public function setLength($length);

    /**
     * Returns outer_width field
     *
     * @return int|null
     */
    public function getOuterWidth();

    /**
     * @param float $width
     *
     * @return $this
     */
    public function setOuterWidth($width);

    /**
     * Returns outer_height field
     *
     * @return int|null
     */
    public function getOuterHeight();

    /**
     * @param float $height
     *
     * @return $this
     */
    public function setOuterHeight($height);

    /**
     * Returns outer_length field
     *
     * @return mixed
     */
    public function getOuterLength();

    /**
     * @param $length
     *
     * @return mixed
     */
    public function setOuterLength($length);

    /**
     * Returns unit field
     *
     * @return mixed
     */
    public function getUnit();

    /**
     * @param $unit
     *
     * @return mixed
     */
    public function setUnit($unit);
}
