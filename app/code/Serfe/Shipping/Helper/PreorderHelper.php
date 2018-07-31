<?php

namespace Serfe\Shipping\Helper;

/**
 * Helper to create PreorderHelper from quote's data
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class PreorderHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     *
     * @var \Magento\Customer\Model\Session 
     */
    protected $customerSession;

    /**
     *
     * @var \Serfe\Shipping\Model\PreorderFactory 
     */
    protected $preorderFactory;

    /**
     *
     * @var \Serfe\Shipping\Api\PreorderRepositoryInterface 
     */
    protected $preorderRepository;

    /**
     *
     * @var \Serfe\Shipping\Model\ResourceModel\Preorder\CollectionFactory 
     */
    protected $preorderCollectionFactory;

    /**
     * @var \Serfe\Shipping\Helper\CustomerHelper 
     */
    protected $customerHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Serfe\Shipping\Model\PreorderFactory $preorderFactory
     * @param \Serfe\Shipping\Api\PreorderRepositoryInterface $preorderRepository
     * @param \Serfe\Shipping\Model\ResourceModel\Preorder\CollectionFactory $preorderCollectionFactory
     * @param \Serfe\Shipping\Helper\CustomerHelper $customerHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Serfe\Shipping\Model\PreorderFactory $preorderFactory,
        \Serfe\Shipping\Api\PreorderRepositoryInterface $preorderRepository,
        \Serfe\Shipping\Model\ResourceModel\Preorder\CollectionFactory $preorderCollectionFactory,
        \Serfe\Shipping\Helper\CustomerHelper $customerHelper
    ) {
        $this->customerSession = $customerSession;
        $this->preorderFactory = $preorderFactory;
        $this->preorderRepository = $preorderRepository;
        $this->preorderCollectionFactory = $preorderCollectionFactory;
        $this->customerHelper = $customerHelper;

        parent::__construct($context);
    }

    /**
     * Create Preorder
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return boolean
     */
    public function createPreorder(\Magento\Quote\Model\Quote $quote)
    {
        $preorderData = $this->getPreorderData($quote);
        $preorder = $this->preorderFactory->create();
        $preorder->addData($preorderData);

        try {
            $created = $this->preorderRepository->save($preorder);
        } catch (\Exception $exc) {
            $this->_logger->error($exc->getMessage());
            $created = false;
        }

        return $created;
    }

    /**
     * Get Preorder data
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    protected function getPreorderData(\Magento\Quote\Model\Quote $quote)
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $quoteId = $quote->getId();
        $addressId = $this->customerHelper->getCustomerDefaultShipping($customerId);


        $preorderData = [
            \Serfe\Shipping\Api\Data\PreorderInterface::IS_AVAILABLE => false,
            \Serfe\Shipping\Api\Data\PreorderInterface::CUSTOMER_ID => $customerId,
            \Serfe\Shipping\Api\Data\PreorderInterface::SHIPPING_METHOD => $shippingMethod,
            \Serfe\Shipping\Api\Data\PreorderInterface::QUOTE_ID => $quoteId,
            \Serfe\Shipping\Api\Data\PreorderInterface::ADDRESS_ID => $addressId
        ];

        return $preorderData;
    }

    /**
     * Check if the current customer has a preorder available
     *
     * @param string $shippingCode
     * @return boolean
     */
    public function hasPreorderAvailable($shippingCode = '')
    {
        $hasPreorderAvailable = false;
        $customerId = $this->customerSession->getCustomer()->getId();
        $preorderCollection = $this->preorderCollectionFactory->create();
        $preorderCollection
            ->addFieldToFilter(\Serfe\Shipping\Api\Data\PreorderInterface::CUSTOMER_ID, $customerId)
            ->addFieldToFilter(\Serfe\Shipping\Api\Data\PreorderInterface::IS_AVAILABLE, \Serfe\Shipping\Model\Preorder::AVAILABLE);
        if ($shippingCode) {
            $preorderCollection->addFieldToFilter(\Serfe\Shipping\Api\Data\PreorderInterface::SHIPPING_METHOD, ['like' => '%' . $shippingCode]);
        }
        $preorderCollectionSize = $preorderCollection->getSize();

        if ($preorderCollectionSize) {
            $hasPreorderAvailable = true;
        }

        return $hasPreorderAvailable;
    }

    /**
     * Get Preorder id by shipping code, for the current customer
     *
     * @param string $shippingCode
     * @return string|int
     */
    protected function getPreorderId($shippingCode)
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $preorderCollection = $this->preorderCollectionFactory->create();
        $preorder = $preorderCollection
            ->addFieldToFilter(\Serfe\Shipping\Api\Data\PreorderInterface::CUSTOMER_ID, $customerId)
            ->addFieldToFilter(\Serfe\Shipping\Api\Data\PreorderInterface::IS_AVAILABLE, \Serfe\Shipping\Model\Preorder::AVAILABLE)
            ->addFieldToFilter(\Serfe\Shipping\Api\Data\PreorderInterface::SHIPPING_METHOD, ['like' => '%' . $shippingCode])
            ->getLastItem();

        return $preorder->getId();
    }

    /**
     * Returns a preorder object based on the shipping code and current user
     *
     * @param string $shippingCode
     * @return \Serfe\Shipping\Model\Preorder|false
     */
    public function getPreorderByShippingCode($shippingCode)
    {
        $preorder = false;
        if ($this->customerSession->isLoggedIn() && $this->hasPreorderAvailable($shippingCode)) {
            $preorderId = $this->getPreorderId($shippingCode);
            $preorder = $this->preorderRepository->getById($preorderId);
        }

        return $preorder;
    }
}