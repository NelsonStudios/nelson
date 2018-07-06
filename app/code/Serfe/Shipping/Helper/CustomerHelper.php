<?php

namespace Serfe\Shipping\Helper;

/**
 * Helper to create customer
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class CustomerHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     *
     * @var \Magento\Customer\Model\Session 
     */
    protected $session;

    /**
     *
     * @var type 
     */
    protected $storeManager;

    /**
     *
     * @var type 
     */
    protected $customerFactory;

    /**
     *
     * @var type 
     */
    protected $mathRandom;

    /**
     *
     * @var type 
     */
    protected $encryptor;

    /**
     *
     * @var type 
     */
    protected $addressHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Serfe\Shipping\Helper\AddressHelper $addressHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Serfe\Shipping\Helper\AddressHelper $addressHelper
    ) {
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->mathRandom = $mathRandom;
        $this->encryptor = $encryptor;
        $this->addressHelper = $addressHelper;

        parent::__construct($context);
    }

    /**
     * Autologin a user
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $customerEmail
     * @return boolean
     */
    public function autoLoginUser(\Magento\Quote\Model\Quote $quote, $customerEmail)
    {
        $login = true;
        if (!$this->session->isLoggedIn()) {
            $login = $this->loginCurrentUser($quote, $customerEmail);
        }

        return $login;
    }

    /**
     * Login current user
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $customerEmail
     * @return boolean
     */
    protected function loginCurrentUser(\Magento\Quote\Model\Quote $quote, $customerEmail)
    {
        $customer = $this->getCustomerByEmail($customerEmail);
        $login = false;
        if (!$customer) {
            $customer = $this->createUserFromQuote($quote, $customerEmail);
            if ($customer) {
                $customerId = $customer->getId();
                $this->addressHelper->createAddressFromQuote($quote->getShippingAddress(), $customerId);
                $this->session->setCustomerAsLoggedIn($customer);
                $login = true;
            }
        } else {
            $customerId = $customer->getId();
            // Repository does not return the correct type, so we need to load the customer
            $customer = $this->customerFactory->create()->load($customerId);
            $this->session->setCustomerAsLoggedIn($customer);
            $login = true;
        }

        return $login;
    }

    /**
     * Load customer by email
     *
     * @param string $email
     * @return boolean
     */
    protected function getCustomerByEmail($email)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        try {
            $customer = $this->customerRepository->get($email, $websiteId);
        } catch (\Exception $ex) {
            $customer = false;
        }

        return $customer;
    }

    /**
     * Create a user from the quote data
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $customerEmail
     * @return boolean
     */
    protected function createUserFromQuote(\Magento\Quote\Model\Quote $quote, $customerEmail)
    {
        // Get Website ID
        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        $customerData = $this->getUserData($quote, $customerEmail);
        // Instantiate object (this is the most important part)
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        // Preparing data for new customer
        $customer->addData($customerData);
        try {
            $customer->save();
            $newCustomer = $customer;
        } catch (\Exception $exc) {
            $newCustomer = false;
        }

        return $newCustomer;
    }

    /**
     * Get user data
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $customerEmail
     * @return array
     */
    protected function getUserData(\Magento\Quote\Model\Quote $quote, $customerEmail)
    {
        $customerData = [
            'email' => $customerEmail,
            'firstname' => $quote->getShippingAddress()->getFirstname(),
            'lastname' => $quote->getShippingAddress()->getLastname(),
            'password' => $this->mathRandom->getRandomString(15),
            'group_id' => 4 //Autoregistered users
        ];

        return $customerData;
    }
}