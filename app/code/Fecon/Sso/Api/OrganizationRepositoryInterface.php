<?php


namespace Fecon\Sso\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface OrganizationRepositoryInterface
{

    /**
     * Save Organization
     * @param \Fecon\Sso\Api\Data\OrganizationInterface $organization
     * @return \Fecon\Sso\Api\Data\OrganizationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Fecon\Sso\Api\Data\OrganizationInterface $organization
    );

    /**
     * Retrieve Organization
     * @param string $organizationId
     * @return \Fecon\Sso\Api\Data\OrganizationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($organizationId);

    /**
     * Retrieve Organization matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Fecon\Sso\Api\Data\OrganizationSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Organization
     * @param \Fecon\Sso\Api\Data\OrganizationInterface $organization
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Fecon\Sso\Api\Data\OrganizationInterface $organization
    );

    /**
     * Delete Organization by ID
     * @param string $organizationId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($organizationId);
}
