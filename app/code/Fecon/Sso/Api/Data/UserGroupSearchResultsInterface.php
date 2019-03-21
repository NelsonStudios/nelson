<?php


namespace Fecon\Sso\Api\Data;

interface UserGroupSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get UserGroup list.
     * @return \Fecon\Sso\Api\Data\UserGroupInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Fecon\Sso\Api\Data\UserGroupInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
