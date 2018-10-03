<?php

namespace Fecon\Shipping\Controller\Order;

/**
 * Description of Success
 *
 *
 */
class Success extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $rP = $this->resultPageFactory->create();
        //page title for browser
        $rP->getConfig()->getTitle()->set('Fecon - You have received your request successfully');
        return $rP;
    }
}
