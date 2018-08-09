<?php


namespace Fecon\SytelineIntegration\Api\Data;

interface SubmissionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Submission list.
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface[]
     */
    public function getItems();

    /**
     * Set order_id list.
     * @param \Fecon\SytelineIntegration\Api\Data\SubmissionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
