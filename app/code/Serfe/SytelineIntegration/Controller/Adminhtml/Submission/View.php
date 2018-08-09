<?php

namespace Serfe\SytelineIntegration\Controller\Adminhtml\Submission;

/**
 * View Submission on Backend
 *
 * 
 */
class View extends \Magento\Backend\App\Action
{
    /**
     * Page Factory
     *
     * @var \Magento\Framework\View\Result\PageFactory 
     */
    protected $resultPageFactory;

    /**
     * Submission Repository
     *
     * @var \Serfe\SytelineIntegration\Api\SubmissionRepositoryInterface 
     */
    protected $submissionRepository;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context                           $context
     * @param \Magento\Framework\View\Result\PageFactory                    $resultPageFactory
     * @param \Serfe\SytelineIntegration\Api\SubmissionRepositoryInterface  $submissionRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context                             $context,
        \Magento\Framework\View\Result\PageFactory                      $resultPageFactory,
        \Magento\Framework\Registry                                     $coreRegistry,
        \Serfe\SytelineIntegration\Api\SubmissionRepositoryInterface    $submissionRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->submissionRepository = $submissionRepository;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $submissionId = $this->getRequest()->getParam('submission_id');
        $submissionLoaded = $this->loadSubmission($submissionId);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__("View Submission"));
        if (!$submissionLoaded) {
            $this->messageManager->addError(__('This submission no longer exists.'));
            $this->_redirect('*/submission/');
            $resultPage = null;
        }

        return $resultPage;
    }

    /**
     * Load Submission into Core Registry
     *
     * @param string $submissionId
     * @return boolean
     */
    protected function loadSubmission($submissionId)
    {
        try {
            $submission = $this->submissionRepository->getById($submissionId);
            $this->coreRegistry->register('submission', $submission);
            $success = true;
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $success = false;
        }

        return $success;
    }
}