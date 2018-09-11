<?php

namespace Fecon\Shipping\Helper;

use Fecon\Shipping\Api\Data\PreorderInterface;

/**
 * Helper to create PreorderHelper from quote's data
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
     * @var \Fecon\Shipping\Model\PreorderFactory 
     */
    protected $preorderFactory;

    /**
     *
     * @var \Fecon\Shipping\Api\PreorderRepositoryInterface 
     */
    protected $preorderRepository;

    /**
     *
     * @var \Fecon\Shipping\Model\ResourceModel\Preorder\CollectionFactory 
     */
    protected $preorderCollectionFactory;

    /**
     * @var \Fecon\Shipping\Helper\CustomerHelper 
     */
    protected $customerHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Fecon\Shipping\Model\PreorderFactory $preorderFactory
     * @param \Fecon\Shipping\Api\PreorderRepositoryInterface $preorderRepository
     * @param \Fecon\Shipping\Model\ResourceModel\Preorder\CollectionFactory $preorderCollectionFactory
     * @param \Fecon\Shipping\Helper\CustomerHelper $customerHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Fecon\Shipping\Model\PreorderFactory $preorderFactory,
        \Fecon\Shipping\Api\PreorderRepositoryInterface $preorderRepository,
        \Fecon\Shipping\Model\ResourceModel\Preorder\CollectionFactory $preorderCollectionFactory,
        \Fecon\Shipping\Helper\CustomerHelper $customerHelper
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
            PreorderInterface::IS_AVAILABLE => false,
            PreorderInterface::CUSTOMER_ID => $customerId,
            PreorderInterface::SHIPPING_METHOD => $shippingMethod,
            PreorderInterface::QUOTE_ID => $quoteId,
            PreorderInterface::ADDRESS_ID => $addressId
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
            ->addFieldToFilter(PreorderInterface::CUSTOMER_ID, $customerId)
            ->addFieldToFilter(PreorderInterface::IS_AVAILABLE, \Fecon\Shipping\Model\Preorder::AVAILABLE);
        if ($shippingCode) {
            $preorderCollection->addFieldToFilter(PreorderInterface::SHIPPING_METHOD, ['like' => '%' . $shippingCode]);
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
            ->addFieldToFilter(PreorderInterface::CUSTOMER_ID, $customerId)
            ->addFieldToFilter(PreorderInterface::IS_AVAILABLE, \Fecon\Shipping\Model\Preorder::AVAILABLE)
            ->addFieldToFilter(PreorderInterface::SHIPPING_METHOD, ['like' => '%' . $shippingCode])
            ->getLastItem();

        return $preorder->getId();
    }

    /**
     * Returns a preorder object based on the shipping code and current user
     *
     * @param string $shippingCode
     * @return \Fecon\Shipping\Model\Preorder|false
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

    /**
     * Marks a Preorder entity as complete (change status)
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Fecon\Shipping\Model\Preorder|false
     */
    public function completePreorder($quote)
    {
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $preorder = $this->getPreorderByShippingCode($shippingMethod);
        if ($preorder) {
            $preorder->setData(PreorderInterface::STATUS, PreorderInterface::STATUS_COMPLETED);
            $preorder->setData(PreorderInterface::IS_AVAILABLE, \Fecon\Shipping\Model\Preorder::NOT_AVAILABLE);

            try {
                $updated = $this->preorderRepository->save($preorder);
                $customerId = $preorder->getData(PreorderInterface::CUSTOMER_ID);
                $this->customerHelper->clearOrderTokenToCustomer($customerId);
            } catch (\Exception $exc) {
                $this->_logger->error($exc->getMessage());
                $updated = false;
            }
        }

        return $updated;
    }
}