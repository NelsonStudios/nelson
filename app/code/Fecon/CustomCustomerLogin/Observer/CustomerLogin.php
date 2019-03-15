<?php

namespace Fecon\CustomCustomerLogin\Observer;

use Magento\Framework\Event\ObserverInterface;
use Fecon\CustomCustomerLogin\Helper\Data;

/**
 * CustomerLogin class to execute extra actions after customer login success.
 */
class CustomerLogin implements ObserverInterface
{

    /**
     * [$origin description]
     * @var [type]
     */
    protected $origin;
    /**
     * [$tek description]
     * @var [type]
     */
    protected $tek;
    protected $helper;

    /**
     * Construct
     */
    public function __construct(
        \Fecon\CustomCustomerLogin\Helper\Data $customCustomerLoginHelper
    ) {
        $this->helper = $customCustomerLoginHelper;
    }

    /**
     * Execute function to handle post-login:
     * 
     * Here the system will try to:
     * 
     * - Validate if user has a Documoto account
     *   - If it has Documoto account:
     *     - Perform login using Documoto Single Sign On (SSO)
     *       - If query string with "redirect=1" comes the customer must be redirected to Documoto Site.
     *       - Otherwise will stay in Magento 2 site.   
     *   - If user doesn't have Documoto account.
     *     - Redirect user to "Join our Dealer's Program" page.
     * 
     * @param  \Magento\Framework\Event\Observer $observer
      * @return string
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        // echo "Customer LoggedIn";
        // $customer = $observer->getEvent()->getCustomer();
        // echo $customer->getName(); //Get customer name
        // exit;
        // $customer = $observer->getEvent()->getCustomer();
        
        // $data = $this->helper->checkDocumotoUser($customer);
        // if($this->checkDocumotoUser($customer)) {// Check if it's a Documoto user
            //// Collect customer data
            
            /**
             *  Regarding documentation we'll need to provide for SSO login:
             *  - Attribute used to send Documoto User Group.
             *  - Attribute used to send Documoto Organization.
             *  - Attribute used to send Documoto UserName (Specifically called NameID).
             *  - Attribute used to send Documoto Email Address.
             */
            
            // try {
            //     $isLoggedIn = $this->loginDocumotoUserSSO();
            //     if($isLoggedIn) {
            //         $this->redirectToDocumotoSite();
            //     } else {
            //         $this->redirectToDocumotoNoAccountPage();
            //     }
            // } catch(\Exception $e) {
            //     return $e->getMessage();
            // }

        // }
    }
}