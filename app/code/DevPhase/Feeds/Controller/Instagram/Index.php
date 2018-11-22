<?php

namespace DevPhase\Feeds\Controller\Instagram;

use \DevPhase\Feeds\Helper\FeconWidgetGetterInstagram;

/**
 * Endpoint to get instagram feed data
 *
 * @author  <fecon.com>
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     *
     * @var \Magento\Framework\View\Result\PageFactory 
     */
    protected $resultPageFactory;

    /**
     *
     * @var \Magento\Framework\Json\Helper\Data 
     */
    protected $jsonHelper;

    /**
     * Instagram Helper
     *
     * @var \DevPhase\Feeds\Helper\FeconWidgetGetterInstagram 
     */
    protected $instagramHelper;

    /**
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory  $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \DevPhase\Feeds\Helper\FeconWidgetGetterInstagram $instagramHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \DevPhase\Feeds\Helper\FeconWidgetGetterInstagram $instagramHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->instagramHelper = $instagramHelper;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }
        try {
            $feed = $this->instagramHelper->get();
            return $this->jsonResponse($feed);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '') {
        return $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($response)
        );
    }

}