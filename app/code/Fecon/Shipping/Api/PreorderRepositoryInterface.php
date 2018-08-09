<?php


namespace Fecon\Shipping\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PreorderRepositoryInterface
{


    /**
     * Save Preorder
     * @param \Fecon\Shipping\Api\Data\PreorderInterface $preorder
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Fecon\Shipping\Api\Data\PreorderInterface $preorder
    );

    /**
     * Retrieve Preorder
     * @param string $preorderId
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($preorderId);

    /**
     * Retrieve Preorder matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Fecon\Shipping\Api\Data\PreorderSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Preorder
     * @param \Fecon\Shipping\Api\Data\PreorderInterface $preorder
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Fecon\Shipping\Api\Data\PreorderInterface $preorder
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
