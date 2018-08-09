<?php

namespace Fecon\SytelineIntegration\Helper;

/**
 * Save Submission entity to  the database
 *
 * 
 */
class SubmissionHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Submission Factory
     *
     * @var \Fecon\SytelineIntegration\Model\SubmissionFactory 
     */
    protected $submissionFactory;

    /**
     * Submission Repository
     *
     * @var \Fecon\SytelineIntegration\Api\SubmissionRepositoryInterface 
     */
    protected $submissionRepository;

    /**
     * Config Helper
     *
     * @var \Fecon\SytelineIntegration\Helper\ConfigHelper 
     */
    protected $configHelper;

    /**
     * Logger
     *
     * @var \Fecon\SytelineIntegration\Logger\Handler 
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Fecon\SytelineIntegration\Model\SubmissionFactory $submissionFactory,
        \Fecon\SytelineIntegration\Api\SubmissionRepositoryInterface $submissionRepository,
        \Fecon\SytelineIntegration\Helper\ConfigHelper $configHelper,
        \Fecon\SytelineIntegration\Logger\Logger $logger
    ) {
        parent::__construct($context);

        $this->submissionFactory = $submissionFactory;
        $this->submissionRepository = $submissionRepository;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
    }

    /**
     * Create and save a Submission entity
     *
     * @param string $orderId
     * @param array $request
     * @param \stdClass $response
     * @param boolean $successfullRequest
     * @param array $errors
     * @return boolean
     */
    public function createSubmission($orderId, $request, $response, $successfullRequest, $errors = null)
    {
        $submission = $this->submissionFactory->create();
        $testingMode = $this->configHelper->isTestModeEnabled();
        $requestStr = print_r($request, true);
        $responseStr = print_r($response, true);
        $errorsStr = $errors ? print_r($errors, true) : $errors;
        $submission->setOrderId($orderId);
        $submission->setSuccess($successfullRequest);
        $submission->setTesting($testingMode);
        $submission->setRequest($requestStr);
        $submission->setResponse($responseStr);
        $submission->setErrors($errorsStr);

        try {
            $this->submissionRepository->save($submission);
            $success = true;
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $success = false;
            $this->logger->err('Could not create submission entity for order id: ' . $orderId . ', exception message: ' . $ex->getMessage());
        }

        return $success;
    }
}