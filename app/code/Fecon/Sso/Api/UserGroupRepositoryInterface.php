<?php


namespace Fecon\Sso\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface UserGroupRepositoryInterface
{

    /**
     * Save UserGroup
     * @param \Fecon\Sso\Api\Data\UserGroupInterface $userGroup
     * @return \Fecon\Sso\Api\Data\UserGroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Fecon\Sso\Api\Data\UserGroupInterface $userGroup
    );

    /**
     * Retrieve UserGroup
     * @param string $usergroupId
     * @return \Fecon\Sso\Api\Data\UserGroupInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($usergroupId);

    /**
     * Retrieve UserGroup matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Fecon\Sso\Api\Data\UserGroupSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete UserGroup
     * @param \Fecon\Sso\Api\Data\UserGroupInterface $userGroup
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Fecon\Sso\Api\Data\UserGroupInterface $userGroup
    );

    /**
     * Delete UserGroup by ID
     * @param string $usergroupId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($usergroupId);
}
