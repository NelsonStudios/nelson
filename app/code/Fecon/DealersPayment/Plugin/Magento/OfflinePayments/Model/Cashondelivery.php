<?php

namespace Fecon\DealersPayment\Plugin\Magento\OfflinePayments\Model;

/**
 * Plugin for the Cash On Delivery payment method
 */
class Cashondelivery
{

    /**
     * @var \Magento\Customer\Model\Session 
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
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
    ) {
        $isDocumotoUser = $this->isDocumotoUser();
        $resultNewValue = $result && $isDocumotoUser;

        return $resultNewValue;
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
            $isDocumotoUser = (bool) $customer->getCustomAttribute('is_documoto_user')->getValue();
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
}