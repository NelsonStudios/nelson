<?php

namespace Fecon\SytelineIntegration\Helper;

/**
 * Helper to manage Customer Syteline's addresses
 */
class AddressHelper
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Fecon\SytelineIntegration\Helper\SytelineHelper
     */
    protected $sytelineHelper;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $regionFactory;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    protected $dataRegionFactory;

    /**
     * @var \Fecon\SytelineIntegration\Helper\ConfigHelper
     */
    protected $configHelper;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\Session $session
     * @param \Fecon\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $dataRegionFactory
     * @param \Fecon\SytelineIntegration\Helper\ConfigHelper $configHelper
     */
    public function __construct(
        \Magento\Customer\Model\Session $session,
        \Fecon\SytelineIntegration\Helper\SytelineHelper $sytelineHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $dataRegionFactory,
        \Fecon\SytelineIntegration\Helper\ConfigHelper $configHelper
    ) {
        $this->session = $session;
        $this->sytelineHelper = $sytelineHelper;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->countryFactory = $countryFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->regionFactory = $regionFactory;
        $this->dataRegionFactory = $dataRegionFactory;
        $this->configHelper = $configHelper;
    }

    /**
     * Sync the Syteline's addresses of the current logged-in customer
     */
    public function syncAddresses()
    {
        $sytelineAddresses = $this->sytelineHelper->getCustomerSytelineAddresses();
        $customer = $this->getCustomer();
        if ($customer && !$this->hasDefaultSytelineId($customer)) {
            $customerAddresses = $customer->getAddresses();
            $this->sync($customerAddresses, $sytelineAddresses, $customer);
        }
    }

    /**
     * Get current customer logged customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null    Returns null if customer is not logged-in
     */
    protected function getCustomer()
    {
        $customer = null;
        $customerId = $this->session->getCustomerId();
        if ($customerId) {
            try {
                $customer = $this->customerRepository->getById($customerId);
            } catch (\Exception $ex) { }
        }

        return $customer;
    }

    /**
     * Sync addresses between Syteline and Magento systems
     *
     * @param \Magento\Customer\Api\Data\AddressInterface[]|null $magentoAddresses
     * @param array $sytelineAddresses
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     */
    protected function sync($magentoAddresses, $sytelineAddresses, $customer)
    {
        foreach ($sytelineAddresses as $sytelineAddress) {
            if (!$this->existsSytelineAddress($sytelineAddress, $magentoAddresses)) {
                $this->saveCustomerAddress($sytelineAddress, $customer);
            }
        }
    }

    /**
     * Check if the Syteline Address is already present in the customer's Magento account
     *
     * @param array $sytelineAddress
     * @param \Magento\Customer\Api\Data\AddressInterface[]|null $magentoAddresses
     * @return boolean
     */
    protected function existsSytelineAddress($sytelineAddress, $magentoAddresses)
    {
        $exists = false;
        if ($magentoAddresses) {
            foreach ($magentoAddresses as $magentoAddress) {
                if ($this->areAddressesEquals($sytelineAddress, $magentoAddress)) {
                    $exists = true;
                    break;
                }
            }
        }

        return $exists;
    }

    /**
     * Validate if addresses are equals
     *
     * @param array $sytelineAddress
     * @param \Magento\Customer\Api\Data\AddressInterface $magentoAddress
     * @return boolean
     */
    protected function areAddressesEquals($sytelineAddress, $magentoAddress)
    {
        $equals = false;
        $streetsLines = $magentoAddress->getStreet();
        $addressAreEqual = true;
        if (is_array($streetsLines)) {
            foreach ($streetsLines as $key => $streetLine) {
                $index = $key + 1;
                if (($index == 4) ||
                    ($sytelineAddress['Line' . $index] != $streetLine)
                ) {
                    $addressAreEqual = false;
                    break;
                }
            }
        } else {
            if (($sytelineAddress['Line2'] !== '') || ($sytelineAddress['Line1'] != $streetsLines)) {
                $addressAreEqual = false;
            }
        }
        $countryName = $this->getCountryName($magentoAddress->getCountryId());
        if ($addressAreEqual &&
            $sytelineAddress['City'] == $magentoAddress->getCity() &&
            $sytelineAddress['State'] == $magentoAddress->getRegion()->getRegionCode() &&
            $sytelineAddress['Zipcode'] == $magentoAddress->getPostcode() &&
            $sytelineAddress['Country'] == $countryName
        ) {
            $equals = true;
        }

        return $equals;
    }

    /**
     * Get country name
     *
     * @param string $countryCode
     * @return string
     */
    protected function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);

        return $country->getName();
    }

    /**
     * Copy Syteline Address into Magento system
     *
     * @param array $sytelineAddress
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     */
    protected function saveCustomerAddress($sytelineAddress, $customer)
    {
        $countryId = $this->getCountryId($sytelineAddress['Country']);
        $region = $this->getRegion($sytelineAddress['State'], $countryId);
        $streets = [$sytelineAddress['Line1']];
        if (!is_null($sytelineAddress['Line2'])) {
            $streets[] = $sytelineAddress['Line2'];
        }
        if (!is_null($sytelineAddress['Line3'])) {
            $streets[] = $sytelineAddress['Line3'];
        }
        $address = $this->addressDataFactory->create();
        $address->setFirstname($customer->getFirstname())
                ->setLastname($customer->getLastname())
                ->setCountryId($countryId)
                ->setRegionId($region->getRegionId())
                ->setRegion($region)
                ->setCity($sytelineAddress['City'])
                ->setPostcode($sytelineAddress['Zipcode'])
                ->setCustomerId($customer->getId())
                ->setStreet($streets)
                ->setTelephone('123')
                ->setCustomAttribute('is_syteline_address', 1);

        $this->addressRepository->save($address);
    }

    /**
     * Get country ID by country name
     *
     * @param string $countryName
     * @return string
     */
    protected function getCountryId($countryName)
    {
        $countryId = 'US';
        $countryCollection = $this->countryCollectionFactory->create();
        foreach ($countryCollection as $country) {
            if ($countryName == $country->getName()) {
                $countryId = $country->getId();
            }
        }

        return $countryId;
    }

    /**
     * Get Region Data object
     *
     * @param string $regionCode
     * @param string $countryId
     * @return \Magento\Customer\Api\Data\RegionInterface
     */
    protected function getRegion($regionCode, $countryId)
    {
        $region = $this->regionFactory->create()->loadByCode($regionCode, $countryId);
        $regionData = $this->dataRegionFactory->create();
        $regionData->setRegion($region->getName())
            ->setRegionCode($region->getCode())
            ->setRegionId($region->getId());

        return $regionData;
    }

    /**
     * Validate if customer has default Syteline Id
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return boolean
     */
    protected function hasDefaultSytelineId($customer)
    {
        $customerId = $customer->getCustomAttribute('customer_number')->getValue();
        $defaultId = $this->configHelper->getDefaultSytelineCustomerId();
        $hasDefaultSytelineId = false;
        if (!$customerId || $customerId === $defaultId) {
            $hasDefaultSytelineId = true;
        }

        return $hasDefaultSytelineId;
    }
}