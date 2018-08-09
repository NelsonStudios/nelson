<?php

namespace Fecon\AskAnExpert\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

   
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Fecon_AskAnExpert::askanexpert');
    }

   
    public function execute()
    {
        
       
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Fecon_AskAnExpert::AskAnExpert');
        $resultPage->addBreadcrumb(__('AskAnExpert'), __('AskAnExpert'));
        $resultPage->addBreadcrumb(__('Manage Submissions'), __('Manage Submissions'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Submissions'));

        return $resultPage;
    }
}
