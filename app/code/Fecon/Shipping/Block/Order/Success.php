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
        \Magento\Customer\Model\Session $session,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        parent::__construct($context, $data);
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
        return $this->_session->isLoggedIn();
    }


}
