<?php


namespace Serfe\Shipping\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PreorderRepositoryInterface
{


    /**
     * Save Preorder
     * @param \Serfe\Shipping\Api\Data\PreorderInterface $preorder
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Serfe\Shipping\Api\Data\PreorderInterface $preorder
    );

    /**
     * Retrieve Preorder
     * @param string $preorderId
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($preorderId);

    /**
     * Retrieve Preorder matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Serfe\Shipping\Api\Data\PreorderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Preorder
     * @param \Serfe\Shipping\Api\Data\PreorderInterface $preorder
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Serfe\Shipping\Api\Data\PreorderInterface $preorder
    );

    /**
     * Delete Preorder by ID
     * @param string $preorderId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($preorderId);
}
