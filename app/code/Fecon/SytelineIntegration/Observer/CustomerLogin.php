<?php

namespace Fecon\SytelineIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Sync customer addresses when logged-in
 */
class CustomerLogin implements ObserverInterface
{

    /**
     * @var \Fecon\SytelineIntegration\Helper\AddressHelper
     */
    protected $addressHelper;

    /**
     * Constructor
     *
     * @param \Fecon\SytelineIntegration\Helper\AddressHelper $addressHelper
     * @return void
     */
    public function __construct(\Fecon\SytelineIntegration\Helper\AddressHelper $addressHelper)
    {
        $this->addressHelper = $addressHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->addressHelper->syncAddresses();
    }
}