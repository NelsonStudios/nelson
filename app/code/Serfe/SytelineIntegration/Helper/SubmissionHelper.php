<?php

namespace Serfe\SytelineIntegration\Helper;

/**
 * Save Submission entity to  the database
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class SubmissionHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Submission Factory
     *
     * @var \Serfe\SytelineIntegration\Model\SubmissionFactory 
     */
    protected $submissionFactory;

    /**
     * Submission Repository
     *
     * @var \Serfe\SytelineIntegration\Api\SubmissionRepositoryInterface 
     */
    protected $submissionRepository;

    /**
     * Config Helper
     *
     * @var \Serfe\SytelineIntegration\Helper\ConfigHelper 
     */
    protected $configHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Serfe\SytelineIntegration\Model\SubmissionFactory $submissionFactory,
        \Serfe\SytelineIntegration\Api\SubmissionRepositoryInterface $submissionRepository,
        \Serfe\SytelineIntegration\Helper\ConfigHelper $configHelper
    ) {
        parent::__construct($context);

        $this->submissionFactory = $submissionFactory;
        $this->submissionRepository = $submissionRepository;
        $this->configHelper = $configHelper;
    }

    public function createSubmission($request, $response, $successfullRequest, $errors = null)
    {
        $submission = $this->submissionFactory->create();
        $testingMode = $this->configHelper->isTestModeEnabled();
        $requestStr = print_r($request, true);
        $responseStr = print_r($response, true);
        $submission->setSuccess($successfullRequest);
        $submission->setTesting($testingMode);
        $submission->setRequest($requestStr);
        $submission->setResponse($responseStr);
        $submission->setErrors($errors);

        try {
            $this->submissionRepository->save($submission);
            $success = true;
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $success = false;
        }

        return $success;
    }
}