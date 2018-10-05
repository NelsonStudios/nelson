<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ObjectManager;

class Addresses extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_logger = $logger;
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );
    }

    /**
     * Multishipping checkout select address page
     *
     * @return void
     */
    public function execute()
    {
        /* Custom code */
        // Get ObjectManager instance of a customer session and checkout address
        $checkoutSession = ObjectManager::getInstance()->get(\Magento\Checkout\Model\Session::class);
        $customerSession = ObjectManager::getInstance()->get(\Magento\Customer\Model\Session::class);
        $addressRepository = ObjectManager::getInstance()->get(\Magento\Customer\Api\AddressRepositoryInterface::class);
        // Get sessioned virtual addresses
        $addressesIds = $checkoutSession->getVirtualAddressesIds();
        // Get customer data object
        $customer = $customerSession->getCustomerDataObject();
        // Get customer addresses
        $addresses = $customer->getAddresses();
        // Prepare to delete only virtual ones.
        foreach ($addresses as $address) {
            if (!empty($addressesIds) && in_array($address->getId(), $addressesIds)) {
              $this->_logger->debug( 'Delete Address ID: '.$address->getId() );
                //$addressRepository->deleteById($address->getId());
            }
        }
        /* End Custom code */

        // If customer do not have addresses
        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('*/checkout_address/newShipping');
            return;
        }
        /* Custom code */
        $this->_getState()->getSelectedCarriersToSplitStep();
        /* End custom code */
        $this->_getState()->unsCompleteStep(State::STEP_SHIPPING);

        $this->_getState()->setActiveStep(State::STEP_SELECT_ADDRESSES);
        if (!$this->_getCheckout()->validateMinimumAmount()) {
            $message = $this->_getCheckout()->getMinimumAmountDescription();
            $this->messageManager->addNotice($message);
        }
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
