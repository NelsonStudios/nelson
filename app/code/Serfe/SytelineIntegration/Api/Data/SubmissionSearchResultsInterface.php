<?php


namespace Serfe\SytelineIntegration\Api\Data;

interface SubmissionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Submission list.
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface[]
     */
    public function getItems();

    /**
     * Set order_id list.
     * @param \Serfe\SytelineIntegration\Api\Data\SubmissionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
