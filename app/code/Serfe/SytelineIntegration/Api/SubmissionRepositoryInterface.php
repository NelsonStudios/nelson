<?php


namespace Serfe\SytelineIntegration\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface SubmissionRepositoryInterface
{


    /**
     * Save Submission
     * @param \Serfe\SytelineIntegration\Api\Data\SubmissionInterface $submission
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Serfe\SytelineIntegration\Api\Data\SubmissionInterface $submission
    );

    /**
     * Retrieve Submission
     * @param string $submissionId
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($submissionId);

    /**
     * Retrieve Submission matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Submission
     * @param \Serfe\SytelineIntegration\Api\Data\SubmissionInterface $submission
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Serfe\SytelineIntegration\Api\Data\SubmissionInterface $submission
    );

    /**
     * Delete Submission by ID
     * @param string $submissionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($submissionId);
}
