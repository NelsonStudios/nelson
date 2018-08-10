<?php

namespace Fecon\Shipping\Block\Adminhtml\Preorder\Edit;

use Fecon\Shipping\Api\Data\PreorderInterface;

/**
 * Block to retrieve Customer Information
 */
class CustomerInformation extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry 
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $customer;

    /**
     * Group service
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface 
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        array $data = array()
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->customer = null;
        $this->customerRepository = $customerRepository;
        $this->groupRepository = $groupRepository;

        parent::__construct($context, $data);
    }

    /**
     * Get customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getCustomer()
    {
        if (!$this->customer) {
            $preorder = $this->coreRegistry->registry('fecon_shipping_preorder');
            $customerId = $preorder->getData(PreorderInterface::CUSTOMER_ID);
            $this->customer = $this->customerRepository->getById($customerId);
        }

        return $this->customer;
    }

    /**
     * Get Customer View Url
     *
     * @return string
     */
    public function getCustomerViewUrl()
    {
        return $this->getUrl('customer/index/edit', ['id' => $this->getCustomer()->getId()]);
    }

    /**
     * @return string
     */
    public function getCustomerName()
    {
        $customerName = $this->getCustomer()->getFirstname() . ' ' . $this->getCustomer()->getLastname();

        return $this->escapeHtml($customerName);
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        $customerEmail = $this->getCustomer()->getEmail();

        return $this->escapeHtml($customerEmail);
    }

    /**
     * Return name of the customer group.
     *
     * @return string
     */
    public function getCustomerGroupName()
    {
        if ($this->getOrder()) {
            $customerGroupId = $this->getCustomer()->getGroupId();
            try {
                if ($customerGroupId !== null) {
                    return $this->groupRepository->getById($customerGroupId)->getCode();
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return '';
            }
        }

        return '';
    }
}