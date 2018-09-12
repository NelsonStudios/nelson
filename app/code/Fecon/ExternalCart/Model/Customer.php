<?php
/**
 * Contributor company: Fecon.
 * Contributor Author : <fecon.com>
 * Date: 2018/08/02
 */
namespace Fecon\ExternalCart\Model;

use Fecon\ExternalCart\Api\CustomerInterface;
 
/**
 * Defines the implementaiton class of the CustomerInterface
 */
class Customer implements CustomerInterface {
    /**
     * integrationCustomerTokenServiceV1
     * 
     * @var string
     */
    protected $integrationCustomerTokenServiceV1;
    /**
     * customerCustomerRepositoryV1
     * 
     * @var string
     */
    protected $customerCustomerRepositoryV1;
    /**
     * $customerData 
     * 
     * @var stdClass
     */
    protected $customerData;
    /**
     * $coreSession
     * 
     * @var \Magento\Framework\Session\SessionManagerInterface 
     */
    protected $coreSession;
    /**
     * $customerSession
     * 
     * @var \Magento\Customer\Model\Session 
     */
    protected $customerSession;
    /**
     * $customerCollection
     * 
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected $customerCollection;
    /**
     * $customerAddressFactory
     * 
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;
    /**
     * $customerFactory
     * 
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    /**
     * $countryFactory
     * 
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;
    /**
     * $regionFactory
     * 
     * @var \Magento\Directory\Model\regionFactory
     */
    protected $regionFactory;
    /**
     * $request
     * 
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * $externalCartHelper
     * 
     * @var \Fecon\ExternalCart\Helper\Data 
     */
    protected $externalCartHelper;
    /**
     * $protocol
     * 
     * @var string
     */
    protected $protocol;
    /**
     * $hostname
     * 
     * @var string
     */
    protected $hostname;
    /**
     * $port 
     * 
     * @var string
     */
    protected $port;
    /**
     * The "full domain" with protocol + domain + port
     * @var string
     */
    public $origin;

    /**
     * Constructor
     * 
     * @param \Magento\Framework\Session\SessionManagerInterface        $coreSession           
     * @param \Magento\Customer\Model\Session                           $customerSession       
     * @param \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection    
     * @param \Magento\Customer\Model\AddressFactory                    $customerAddressFactory
     * @param \Magento\Customer\Model\CustomerFactory                   $customerFactory       
     * @param \Magento\Directory\Model\CountryFactory                   $countryFactory        
     * @param \Magento\Directory\Model\RegionFactory                    $regionFactory         
     * @param \Magento\Framework\App\Request\Http                       $request               
     * @param \Fecon\ExternalCart\Helper\Data                           $externalCartHelper    
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\ResourceModel\Customer\Collection $customerCollection,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\App\Request\Http $request,
        \Fecon\ExternalCart\Helper\Data $externalCartHelper
    ) { 
        $this->cartHelper = $externalCartHelper;
        /**
         * First check if it's allowed to use the API.
         */
        $this->cartHelper->checkAllowed();

        $this->coreSession = $coreSession;
        $this->customerSession = $customerSession;
        $this->customerCollection = $customerCollection;
        $this->customerFactory = $customerFactory;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->countryFactory = $countryFactory;
        $this->regionFactory = $regionFactory;
        $this->request = $request;

        $this->protocol = $this->cartHelper->protocol();
        $this->hostname = $this->cartHelper->hostname();
        $this->port = $this->cartHelper->port();

        if(!empty($this->protocol) && !empty($this->hostname)) {
            $this->origin = $this->protocol . $this->hostname;
        }
        if(!empty($this->port)) {
            $this->origin .= ':' . $this->port;
        }
        /* Add backend settings validation */
        if(empty($this->origin)) {
            throw new \Exception(
                __('Please check External Cart Settings in Admin section.')
            );
        }

        /* Integration service V1 */
        $this->integrationCustomerTokenServiceV1 = 'integrationCustomerTokenServiceV1';
        $this->customerCustomerRepositoryV1 = 'customerCustomerRepositoryV1';
    }

    /**
     * customerLogIn
     *
     * @api
     * @return string $customerToken The token of logged-in customer.
     */
    public function customerLogIn() {
        /* Get post data */
        $postData = $this->request->getPost();
        $opts = [
            'soap_version' => SOAP_1_2,
            'trace' => 1,
            'connection_timeout' => 120,
        ];
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->integrationCustomerTokenServiceV1, $opts);
        try {
            $customerToken = $client->integrationCustomerTokenServiceV1CreateCustomerAccessToken($postData);
            $this->customerSession->setData('loggedInUserToken', $customerToken->result);
            return $customerToken->result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * getCustomerData function to perform customer login using Magento 2 REST API
     * This wrapper will log-in the customer and return the token, also will save information to be
     * used in cart session for already logged-in customer.
     * 
     * @api
     * @return string $customerData The data of logged-in customer.
     */
    public function getCustomerData() {
        $loggedInUserToken = $this->customerSession->getData('loggedInUserToken');
        if($loggedInUserToken) {
            $customerData = $this->cartHelper->makeCurlRequest($this->origin, '/rest/V1/customers/me', $loggedInUserToken);
            $cData = $this->cartHelper->jsonDecode($customerData);
            /* Save customer id in session */
            $this->customerSession->setId($cData['id']);
            return $customerData;
        }
        return false;
    }
    /**
     * Set the token of the recently created customer
     *
     * @api
     * @param  string $customerId The customerId to save.
     * @return string $customerId
     */
    public function setCustomerToken($customerId) {
        $this->coreSession->start();
        $this->coreSession->setCustomerId($customerId);
        return $this->getCustomerToken();
    }
    /**
     * Get the token of the recently created customer cart
     *
     * @api
     * @return string $token of created customer cart or empty array otherwise.
     */
    public function getCustomerToken() {
        $this->coreSession->start();
        return $this->coreSession->getCustomerId();
    }
    /**
     * Get the customer data customer
     *
     * @api
     * @param  string $documotoCustomerId The customerId to save.
     * @return string $customerData
     */
    public function getCustomerByDocumotoId($documotoCustomerId) {
        //$documotoCustomerId = $this->request->getParam('customerId');// Enable for testing only.
        try {
            $collection = $this->customerCollection
              ->addAttributeToSelect(array('id', 'firstname', 'customer_id',  'email'))
              ->addAttributeToFilter('customer_id', array('eq' => $documotoCustomerId))
              ->load();
        } catch(\Excepetion $e) {
            return $e->getMessage();
        }
        
        if(count($collection->getData()) === 1) {
            return $collection->getData();
        } else {
            throw new \Exception(
                __('Error, there\'re multiple users with same id.')
            );
        }
    }
    /**
     * Set the customer address
     * State code must be formated as ISO "ALPHA-2 Code"
     *
     * @api
     * @param  string $customerData The customerData to search.
     * @param  string $customerAddressData The customerAddressData to save.
     * @return boolean true on success or throws error on failure.
     */
    public function setCustomerAddress($customerData, $customerAddressData, $addressType) {
        $address = null;
        /* Load customer by id */
        $customer = $this->customerFactory->create()->load($customerData['entity_id']);
        // Check if billing address exists
        if($addressType === 'BillTo') {
            $billingAddressId = $customer->getDefaultBilling();
            if($billingAddressId) {
                $address = $this->customerAddressFactory->create()->load($billingAddressId);
            }
        } else {
            // Check if shipping address exists
            $shippingAddressId = $customer->getDefaultShipping();
            if($shippingAddressId) {
                $address = $this->customerAddressFactory->create()->load($shippingAddressId);
            }
        }
        if(empty($address)) {
            $address = $this->customerAddressFactory->create();
        }
        $address->setCustomerId($customerData['entity_id'])
            ->setFirstname($customerData['firstname'])
            ->setLastname($customerData['lastname']);
            //Magento require a phone number value, but is not coming in request from Documoto, we must set a default value:
            //Confirmed with Matt, note https://tracker.serfe.com/view.php?id=56329#c444298
            $address->setTelephone('0');

        if(!empty($customerAddressData[$addressType]['SiteAddress']['Line1'])
        && !empty($customerAddressData[$addressType]['SiteAddress']['City'])
        && !empty($customerAddressData[$addressType]['SiteAddress']['State'])
        && !empty($customerAddressData[$addressType]['SiteAddress']['Country'])
        && !empty($customerAddressData[$addressType]['SiteAddress']['Zipcode'])) {
            $address->setStreet($customerAddressData[$addressType]['SiteAddress']['Line1']);
            $address->setCity($customerAddressData[$addressType]['SiteAddress']['City']);
            
            /* Country id map */
            $country = $this->countryFactory->create()->loadByCode($customerAddressData[$addressType]['SiteAddress']['Country']);
            $countryId = $country->getId();
            $address->setCountryId($countryId);
            
            /* Region id map only for USA counrty */
            if($customerAddressData[$addressType]['SiteAddress']['Country'] === 'USA' || $customerAddressData[$addressType]['SiteAddress']['Country'] === 'US') {
                $region = $this->regionFactory->create()->loadByCode($customerAddressData[$addressType]['SiteAddress']['State'], $countryId);
                $address->setRegionId($region->getId());
            }

            $address->setPostcode($customerAddressData[$addressType]['SiteAddress']['Zipcode']);

            /**
             * Check address type and save in address book.
             */
            if($addressType === 'BillTo') {
                $address->setIsDefaultBilling('1')
                ->setSaveInAddressBook('1');
                
            } else {
                $address->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');
            }
        } else {
            $errorMsg = 'Error, there\'s an error validating customer '. (($addressType === 'BillTo')? 'billing' : 'shipping') .' address. Data sent was: ' . json_encode($customerAddressData[$addressType]['SiteAddress']) . ' data could not be empty.';
            $this->cartHelper->sendAdminErrorNotification($errorMsg);
            throw new \Exception(
                __($errorMsg)
            );
        }
        
        try {
            return $address->save();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}