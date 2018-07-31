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
     * @var \Magento\Store\Model\StoreManagerInterface 
     */
    protected $storeManager;

    /**
     *
     * @var \Magento\Customer\Model\CustomerFactory 
     */
    protected $customerFactory;

    /**
     *
     * @var \Magento\Framework\Math\Random 
     */
    protected $mathRandom;

    /**
     *
     * @var \Magento\Framework\Encryption\EncryptorInterface 
     */
    protected $encryptor;

    /**
     *
     * @var \Serfe\Shipping\Helper\AddressHelper 
     */
    protected $addressHelper;

    /**
     *
     * @var \Serfe\Shipping\Helper\EmailHelper 
     */
    protected $emailHelper;

    /**
     * Customer Repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface 
     */
    protected $customerRepository;

    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime 
     */
    protected $date;

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
     * @param \Serfe\Shipping\Helper\EmailHelper $emailHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Serfe\Shipping\Helper\AddressHelper $addressHelper,
        \Serfe\Shipping\Helper\EmailHelper $emailHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->mathRandom = $mathRandom;
        $this->encryptor = $encryptor;
        $this->addressHelper = $addressHelper;
        $this->emailHelper = $emailHelper;
        $this->customerRepository = $customerRepository;
        $this->date = $date;

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

    /**
     * Create a order token for customer, and send email with it
     *
     * @param string $customerId
     * @return void
     */
    public function addOrderTokenToCustomer($customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        $token = $this->mathRandom->getRandomString(25);
        $datetime = $this->date->gmtDate();
        $customerData = $customer->getDataModel();
        $customerData->setCustomAttribute('order_token', $token);
        $customerData->setCustomAttribute('order_token_created_at', $datetime);
        $customer->updateData($customerData);
        $customer->save();
        $this->emailHelper->sendQuoteAvailableEmail($customer, $token);
    }

    /**
     * Check user's token and login user
     *
     * @param string $customerId
     * @param string $token
     * @return boolean
     */
    public function loginUserByToken($customerId, $token)
    {
        $login = false;
        $customer = $this->customerFactory->create()->load($customerId);
        if ($customer->getOrderToken() == $token) {
            $this->session->setCustomerAsLoggedIn($customer);
            $login = true;
        }

        return $login;
    }

    /**
     * Logout current user
     *
     * @return void
     */
    public function logoutUser()
    {
        if ($this->session->isLoggedIn()) {
            $this->session->logout();
        }
    }

    /**
     * Get default shipping address id
     *
     * @param string $customerId
     * @return string|boolean   Returns false if customer does not exists
     */
    public function getCustomerDefaultShipping($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $shippingAddressId = $customer->getDefaultShipping();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
            $this->_logger->error('Cannot get customer by ID in CustomerHelper, error: ' . $ex->getMessage());
            $shippingAddressId = false;
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $this->_logger->error('Error in CustomerHelper, error: ' . $ex->getMessage());
        }

        return $shippingAddressId;
    }

    /**
     * Get customer name
     *
     * @param string $customerId
     * @return string|boolean   Returns false if customer does not exists
     */
    public function getCustomerName($customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $name = $customer->getFirstname() . ' ' . $customer->getLastname();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
            $this->_logger->error('Cannot get customer by ID in CustomerHelper, error: ' . $ex->getMessage());
            $name = false;
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $this->_logger->error('Error in CustomerHelper, error: ' . $ex->getMessage());
        }

        return $name;
    }
}