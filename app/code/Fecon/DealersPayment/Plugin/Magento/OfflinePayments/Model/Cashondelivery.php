<?php

namespace Fecon\DealersPayment\Plugin\Magento\OfflinePayments\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Plugin for the Cash On Delivery payment method
 */
class Cashondelivery
{
    /**
     *
     */
    const XML_PATH_DEALER_PAYMENT_ALLOW_CUSTOMERS = 'payment/cashondelivery/allow_customer_ids';
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serialize;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session                   $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface                              $config,
        \Magento\Framework\Serialize\Serializer\Json      $serialize
    )
    {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->_config = $config;
        $this->serialize = $serialize;
    }

    /**
     * Plugin for the isActive function
     *
     * @param \Magento\OfflinePayments\Model\Cashondelivery $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsActive(
        \Magento\OfflinePayments\Model\Cashondelivery $subject,
                                                      $result
    )
    {
        $isDocumotoUser = $this->isDocumotoUser();
        $isAllow = $this->isAllowCustomer();
        $resultNewValue = $result && $isDocumotoUser && $isAllow;

        return $resultNewValue;
    }

    /**
     * @return bool
     */
    protected function isAllowCustomer()
    {
        $customer = $this->getCustomer();
        $result = false;
        $allowCustomerIds = $this->getAllowCustomerIds();
        if ($customer && $customer->getId() && $allowCustomerIds) {
            if (in_array($customer->getId(), $allowCustomerIds)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Check if there's a logged-in user, and if it is a Documoto user as well
     *
     * @return bool
     */
    protected function isDocumotoUser()
    {
        $customer = $this->getCustomer();
        $isDocumotoUser = false;
        if ($customer && $customer->getCustomAttribute('is_documoto_user')) {
            $isDocumotoUser = (bool)$customer->getCustomAttribute('is_documoto_user')->getValue();
        }

        return $isDocumotoUser;
    }

    /**
     * Get current logged-in customer (if there's one)
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    protected function getCustomer()
    {
        $customer = null;
        $customerId = null;
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
        }

        if ($customerId) {
            try {
                $customer = $this->customerRepository->getById($customerId);
            } catch (\Exception $ex) {
            }
        }

        return $customer;
    }

    /**
     * @return array
     */
    protected function getAllowCustomerIds()
    {
        $data = $this->_config->getValue(self::XML_PATH_DEALER_PAYMENT_ALLOW_CUSTOMERS);
        $customerIds = array();
        if ($data) {
            $unserializedata = $this->serialize->unserialize($data);
            foreach ($unserializedata as $key => $row) {
                $customerIds[] = $row['customer_id'];
            }
        }
        return $customerIds;
    }
}
