<?php

namespace Fecon\Shipping\Controller\Order;

/**
 * Create preorder action
 *
 * 
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
     * @var \Fecon\Shipping\Helper\CustomerHelper 
     */
    protected $customerHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Fecon\Shipping\Helper\CustomerHelper $customerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Fecon\Shipping\Helper\CustomerHelper $customerHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerHelper = $customerHelper;

        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $token = (string) $this->getRequest()->getParam('token');
        $customerId = (int) $this->getRequest()->getParam('id');
        
        $login = $this->customerHelper->loginUserByToken($customerId, $token);
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $redirectPath = $login ? 'checkout' : '/';
        $resultRedirect->setPath($redirectPath);

        return $resultRedirect;
    }
}