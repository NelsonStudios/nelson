<?php

namespace Fecon\Shipping\Controller\Order;

/**
 * Create Preorder action
 *
 * 
 */
class Create extends \Magento\Framework\App\Action\Action
{

    /**
     *
     * @var \Magento\Framework\View\Result\PageFactory 
     */
    protected $resultPageFactory;

    /**
     *
     * @var \Magento\Checkout\Model\Session 
     */
    protected $_checkoutSession;

    /**
     *
     * @var \Fecon\Shipping\Helper\CustomerHelper 
     */
    protected $customerHelper;

    /**
     *
     * @var \Fecon\Shipping\Helper\PreorderHelper 
     */
    protected $preorderHelper;

    /**
     *
     * @var \Magento\Framework\Json\Helper\Data 
     */
    protected $jsonHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Fecon\Shipping\Helper\CustomerHelper $customerHelper
     * @param \Fecon\Shipping\Helper\PreorderHelper $preorderHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Fecon\Shipping\Helper\CustomerHelper $customerHelper,
        \Fecon\Shipping\Helper\PreorderHelper $preorderHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_checkoutSession = $_checkoutSession;
        $this->customerHelper = $customerHelper;
        $this->preorderHelper = $preorderHelper;
        $this->jsonHelper = $jsonHelper;

        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $success = false;

        if ($this->_checkoutSession->isSessionExists() && $this->_checkoutSession->isSessionExists()) {
            $quote = $this->_checkoutSession->getQuote();
            $email = $this->getRequest()->getParam('email');
            $shouldLogout = !$this->customerHelper->isUserLoggedIn();
            $login = $this->customerHelper->autoLoginUser($quote, $email);
            $preorder = $this->preorderHelper->createPreorder($quote);

            if ($preorder && $login) {
                $success = true;
            }
            if ($shouldLogout) {
                $this->customerHelper->logoutUser();
            }
            $response = [
                'success' => $success
            ];
        }

        try {
            return $this->jsonResponse($response);
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
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($response)
        );
    }
}