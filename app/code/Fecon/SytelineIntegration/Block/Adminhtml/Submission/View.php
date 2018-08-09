<?php

namespace Fecon\SytelineIntegration\Block\Adminhtml\Submission;

/**
 * View Submission Block
 *
 * 
 */
class View extends \Magento\Backend\Block\Template
{
    /**
     * Core Registry
     *
     * @var \Magento\Framework\Registry 
     */
    protected $coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Get Submission
     *
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function getSubmission()
    {
        return $this->coreRegistry->registry('submission');
    }
}