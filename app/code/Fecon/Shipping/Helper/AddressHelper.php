<?php

namespace Fecon\Shipping\Helper;

/**
 * Helper to create Address
 *
 * 
 */
class AddressHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     *
     * @var \Magento\Customer\Model\AddressFactory 
     */
    protected $addressFactory;

    /**
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface 
     */
    protected $addressRepository;

    /**
     * @var \Magento\Directory\Model\CountryFactory 
     */
    protected $countryFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->countryFactory = $countryFactory;

        parent::__construct($context);
    }

    /**
     * Create a new address from quote data
     *
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @param string $customerId
     * @param boolean $isShipping
     */
    public function createAddressFromQuote(
        \Magento\Quote\Model\Quote\Address $quoteAddress,
        $customerId,
        $isShipping = true
    ) {
        $address = $this->addressFactory->create();
        $addressData = $this->getAddressData($quoteAddress);

        $address->setCustomerId($customerId);
        $address->addData($addressData);

        if ($isShipping) {
            $address->setIsDefaultShipping('1');
        } else {
            $address->setIsDefaultBilling('1');
        }
        $address->setSaveInAddressBook('1');

        $address->save();
    }

    /**
     * Transform quote's address into array
     *
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @return array
     */
    protected function getAddressData(\Magento\Quote\Model\Quote\Address $quoteAddress)
    {
        $addressData = [
            'firstname' => $quoteAddress->getFirstname(),
            'lastname' => $quoteAddress->getLastname(),
            'country_id' => $quoteAddress->getCountryId(),
            'postcode' => $quoteAddress->getPostcode(),
            'city' => $quoteAddress->getCity(),
            'telephone' => $quoteAddress->getTelephone(),
            'company' => $quoteAddress->getCompany(),
            'street' => $quoteAddress->getStreet(),
            'region' => $quoteAddress->getRegion(),
            'region_id' => $quoteAddress->getRegionId()
        ];

        return $addressData;
    }

    /**
     * Get address postcode by address id
     *
     * @param string $addressId
     * @return string
     */
    public function getAddressPostcode($addressId)
    {
        try {
            $address = $this->addressRepository->getById($addressId);
            $value = $address->getPostcode();
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $this->_logger->error('Cannot get Address by id in AddressHelper, error: ' . $ex->getMessage());
            $value = '';
        }

        return $value;
    }

    /**
     * Get address state by address id
     *
     * @param string $addressId
     * @return string
     */
    public function getAddressState($addressId)
    {
        try {
            $address = $this->addressRepository->getById($addressId);
            $value = $address->getRegion()->getRegion();
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $this->_logger->error('Cannot get Address by id in AddressHelper, error: ' . $ex->getMessage());
            $value = '';
        }

        return $value;
    }

    /**
     * Get address country by address id
     *
     * @param string $addressId
     * @return string
     */
    public function getAddressCountry($addressId)
    {
        try {
            $address = $this->addressRepository->getById($addressId);
            $countryId = $address->getCountryId();
            $value = $this->getCountryName($countryId);
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $this->_logger->error('Cannot get Address by id in AddressHelper, error: ' . $ex->getMessage());
            $value = '';
        }

        return $value;
    }

    /**
     * Get address city by address id
     *
     * @param string $addressId
     * @return string
     */
    public function getAddressCity($addressId)
    {
        try {
            $address = $this->addressRepository->getById($addressId);
            $value = $address->getCity();
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $this->_logger->error('Cannot get Address by id in AddressHelper, error: ' . $ex->getMessage());
            $value = '';
        }

        return $value;
    }

    /**
     * Get Country name
     *
     * @param string $countryCode
     * @return string
     */
    protected function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);

        return $country->getName();
    }
}