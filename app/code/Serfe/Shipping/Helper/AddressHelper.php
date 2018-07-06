<?php

namespace Serfe\Shipping\Helper;

/**
 * Helper to create Address
 *
 * @author Xuan Villagran <xuan@serfe.com>
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
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;

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
            'street' => $quoteAddress->getStreet()
        ];
        
        return $addressData;
    }
}