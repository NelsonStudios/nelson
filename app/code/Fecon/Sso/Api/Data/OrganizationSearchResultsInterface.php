<?php


namespace Fecon\Sso\Api\Data;

interface OrganizationSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Organization list.
     * @return \Fecon\Sso\Api\Data\OrganizationInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Fecon\Sso\Api\Data\OrganizationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
