<?php


namespace Serfe\Shipping\Api\Data;

interface PreorderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Preorder list.
     * @return \Serfe\Shipping\Api\Data\PreorderInterface[]
     */
    public function getItems();

    /**
     * Set created_at list.
     * @param \Serfe\Shipping\Api\Data\PreorderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
