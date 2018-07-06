<?php


namespace Serfe\Shipping\Controller\Order;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    
    protected $_checkoutSession;
    
    protected $customerHelper;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Serfe\Shipping\Helper\CustomerHelper $customerHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_checkoutSession = $_checkoutSession;
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
        $email = $this->getRequest()->getParam('email');
        $login = $this->customerHelper->autoLoginUser($this->_checkoutSession->getQuote(), $email);
        
        var_dump($login);
        die();
        return $this->resultPageFactory->create();
    }
}