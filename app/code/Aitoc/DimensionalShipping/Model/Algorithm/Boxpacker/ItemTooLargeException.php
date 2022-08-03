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
 * Class ItemTooLargeException
 * Exception used when an item is too large to pack.
 */
class ItemTooLargeException extends \RuntimeException
{
    /** @var ItemInterface */
    public $item;

    /**
     * ItemTooLargeException constructor.
     *
     * @param string $message
     * @param ItemInterface   $item
     */
    public function __construct($message, ItemInterface $item)
    {
        $this->item = $item;
        parent::__construct($message);
    }

    /**
     * @return ItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }
}
