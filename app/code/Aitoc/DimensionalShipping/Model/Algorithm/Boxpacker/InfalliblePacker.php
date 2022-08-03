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
 * A version of the packer that swallows internal exceptions.
 *
 * @author Doug Wright
 */
class InfalliblePacker extends Packer
{
    /**
     * @var ItemList
     */
    protected $unpackedItems;

    /**
     * InfalliblePacker constructor.
     */
    public function __construct()
    {
        $this->unpackedItems = new ItemList();
        parent::__construct();
    }

    /**
     * Return the items that couldn't be packed.
     *
     * @return ItemList
     */
    public function getUnpackedItems()
    {
        return $this->unpackedItems;
    }

    /**
     * {@inheritdoc}
     */
    public function pack()
    {
        $itemList = clone $this->items;

        while (true) {
            try {
                return parent::pack();
            } catch (ItemTooLargeException $e) {
                $this->unpackedItems->insert($e->getItem());
                $itemList->remove($e->getItem());
                $this->setItems($itemList);
            }
        }
    }
}
