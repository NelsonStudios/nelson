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

    public function createSubmission($data)
    {
        $success = true;
        $submission = $this->submissionFactory->create();
//        $date = $this->dateFactory->create()->gmtDate();
//        $submission->setCreationAt($date);
//        $submission->setUpdatedAt($date);
        $submission->setSuccess($data['success']);
        $submission->setTesting($data['testing']);
        $submission->setRequest($data['request']);
//        $submission->setAttempts($data['attempts']);
        $submission->setResponse($data['response']);
        $submission->setErrors($data['errors']);

        try {
            $this->submissionRepository->save($submission);
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            var_dump($ex->getMessage());
            $success = false;
        }
        
        return $success;
    }
}
