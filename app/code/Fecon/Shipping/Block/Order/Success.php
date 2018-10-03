<?php

namespace Fecon\Shipping\Block\Order;

/**
 * Block for success view
 *
 *
 */
class Success extends \Magento\Framework\View\Element\Template
{
    protected $_storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Return base URL or link based on given path
     */
    public function getUrl($path = '')
    {
      return (empty($path))?$this->_storeManager->getStore()->getBaseUrl():$this->_storeManager->getStore()->getUrl($path);
    }

    /**
     * Return link to My Account
     */
    public function getCustomerAccountUrl()
    {
        return $this->getUrl('customer/account');
    }

    /**
     * Check if customer is logged in
     */
    public function isCustomerLoggedIn()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');

        return $customerSession->isLoggedIn();
    }


}
