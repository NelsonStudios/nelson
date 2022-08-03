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

use Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\Psr\Log\LoggerAwareInterface;
use Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\Psr\Log\LoggerAwareTrait;
use Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\Psr\Log\LogLevel;
use Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\Psr\Log\NullLogger;

/**
 * Actual packer.
 *
 * @author Doug Wright
 */
class Packer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Number of boxes at which balancing weight is deemed not worth it.
     *
     * @var int
     */
    protected $maxBoxesToBalanceWeight = 12;

    /**
     * List of items to be packed.
     *
     * @var ItemList
     */
    protected $items;

    /**
     * List of box sizes available to pack items into.
     *
     * @var BoxList
     */
    protected $boxes;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->items = new ItemList();
        $this->boxes = new BoxList();

        $this->logger = new NullLogger();
    }

    /**
     * Add item to be packed.
     *
     * @param ItemInterface $item
     * @param int  $qty
     */
    public function addItem(ItemInterface $item, $qty = 1)
    {
        for ($i = 0; $i < $qty; ++$i) {
            $this->items->insert($item);
        }
        $this->logger->log(LogLevel::INFO, "added {$qty} x {$item->getDescription()}", ['item' => $item]);
    }

    /**
     * Set a list of items all at once.
     *
     * @param iterable $items
     */
    public function setItems($items)
    {
        if ($items instanceof ItemList) {
            $this->items = clone $items;
        } else {
            $this->items = new ItemList();
            foreach ($items as $item) {
                $this->items->insert($item);
            }
        }
    }

    /**
     * Add box size.
     *
     * @param BoxInterface $box
     */
    public function addBox(BoxInterface $box)
    {
        $this->boxes->insert($box);
        $this->logger->log(LogLevel::INFO, "added box {$box->getReference()}", ['box' => $box]);
    }

    /**
     * Add a pre-prepared set of boxes all at once.
     *
     * @param BoxList $boxList
     */
    public function setBoxes(BoxList $boxList)
    {
        $this->boxes = $boxList;
    }

    /**
     * Number of boxes at which balancing weight is deemed not worth the extra computation time.
     *
     * @return int
     */
    public function getMaxBoxesToBalanceWeight()
    {
        return $this->maxBoxesToBalanceWeight;
    }

    /**
     * Number of boxes at which balancing weight is deemed not worth the extra computation time.
     *
     * @param int $maxBoxesToBalanceWeight
     */
    public function setMaxBoxesToBalanceWeight($maxBoxesToBalanceWeight)
    {
        $this->maxBoxesToBalanceWeight = $maxBoxesToBalanceWeight;
    }

    /**
     * Pack items into boxes.
     *
     * @return PackedBoxList
     */
    public function pack()
    {
        $packedBoxes = $this->doVolumePacking();

        //If we have multiple boxes, try and optimise/even-out weight distribution
        if ($packedBoxes->count() > 1 && $packedBoxes->count() <= $this->maxBoxesToBalanceWeight) {
            $redistributor = new WeightRedistributor($this->boxes);
            $redistributor->setLogger($this->logger);
            $packedBoxes = $redistributor->redistributeWeight($packedBoxes);
        }

        $this->logger->log(LogLevel::INFO, "[PACKING COMPLETED], {$packedBoxes->count()} boxes");

        return $packedBoxes;
    }

    /**
     * Pack items into boxes using the principle of largest volume item first.
     *
     * @throws ItemTooLargeException
     *
     * @return PackedBoxList
     */
    public function doVolumePacking()
    {
        $packedBoxes = new PackedBoxList();

        //Keep going until everything packed
        while ($this->items->count()) {
            $packedBoxesIteration = [];

            //Loop through boxes starting with smallest, see what happens
            foreach ($this->boxes as $box) {
                $volumePacker = new VolumePacker($box, clone $this->items);
                $volumePacker->setLogger($this->logger);
                $packedBox = $volumePacker->pack();
                if ($packedBox->getItems()->count()) {
                    $packedBoxesIteration[] = $packedBox;

                    //Have we found a single box that contains everything?
                    if ($packedBox->getItems()->count() === $this->items->count()) {
                        break;
                    }
                }
            }
            //Find best box of iteration, and remove packed items from unpacked list
            $bestBox = $this->findBestBoxFromIteration($packedBoxesIteration);

            /** @var PackedItem $packedItem */
            foreach ($bestBox->getItems() as $packedItem) {
                $this->items->remove($packedItem->getItem());
            }

            $packedBoxes->insert($bestBox);
        }

        return $packedBoxes;
    }

    /**
     * @param PackedBox[] $packedBoxes
     *
     * @return PackedBox
     */
    protected function findBestBoxFromIteration($packedBoxes)
    {
        //Check iteration was productive
        if (count($packedBoxes) === 0) {
            throw new ItemTooLargeException('Item ' . $this->items->top()->getDescription() . ' is too large to fit into any box', $this->items->top());
        }

        usort($packedBoxes, [$this, 'compare']);

        return array_shift($packedBoxes);
    }

    /**
     * @param PackedBox $boxA
     * @param PackedBox $boxB
     *
     * @return int
     */
    private static function compare(PackedBox $boxA, PackedBox $boxB)
    {
        $choice = $boxB->getItems()->count() <=> $boxA->getItems()->count();

        if ($choice === 0) {
            $choice = $boxB->getVolumeUtilisation() <=> $boxA->getVolumeUtilisation();
        }
        if ($choice === 0) {
            $choice = $boxB->getUsedVolume() <=> $boxA->getUsedVolume();
        }
        if ($choice === 0) {
            $choice = $boxA->getWeight() <=> $boxB->getWeight();
        }

        return $choice;
    }
}
