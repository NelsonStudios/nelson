<?php

namespace Fecon\Shipping\Helper;

use Fecon\Shipping\Api\Data\PreorderInterface;
use Fecon\Shipping\Ui\Component\Create\Form\Shipping\Options;

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
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Fecon\Shipping\Model\PreorderFactory $preorderFactory
     * @param \Fecon\Shipping\Api\PreorderRepositoryInterface $preorderRepository
     * @param \Fecon\Shipping\Model\ResourceModel\Preorder\CollectionFactory $preorderCollectionFactory
     * @param \Fecon\Shipping\Helper\CustomerHelper $customerHelper
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Fecon\Shipping\Model\PreorderFactory $preorderFactory,
        \Fecon\Shipping\Api\PreorderRepositoryInterface $preorderRepository,
        \Fecon\Shipping\Model\ResourceModel\Preorder\CollectionFactory $preorderCollectionFactory,
        \Fecon\Shipping\Helper\CustomerHelper $customerHelper,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->customerSession = $customerSession;
        $this->preorderFactory = $preorderFactory;
        $this->preorderRepository = $preorderRepository;
        $this->preorderCollectionFactory = $preorderCollectionFactory;
        $this->customerHelper = $customerHelper;
        $this->serializer = $serializer;

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
            PreorderInterface::SHIPPING_METHOD => $this->serializer->serialize([$shippingMethod]),
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
//            ->addFieldToFilter(PreorderInterface::SHIPPING_METHOD, ['like' => '%' . $shippingCode])
            ->getLastItem();

        return $preorder->getId();
    }

    public function getPreorder(){
        $customerId = $this->customerSession->getCustomer()->getId();
        if(!$customerId){
            return null;
        }
        $preorderCollection = $this->preorderCollectionFactory->create();
        $preorder = $preorderCollection
            ->addFieldToFilter(PreorderInterface::CUSTOMER_ID, $customerId)
            ->addFieldToFilter(PreorderInterface::IS_AVAILABLE, \Fecon\Shipping\Model\Preorder::AVAILABLE)
            ->getLastItem();

        return $preorder;
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
        if ($this->customerSession->isLoggedIn() && $this->hasPreorderAvailable()) {
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

    public function getPreorderFromOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $customerId = $order->getCustomerId();
        $quoteId = $order->getQuoteId();
        $preorderCollection = $this->preorderCollectionFactory->create();
        $preorderId = $preorderCollection
            ->addFieldToFilter(PreorderInterface::CUSTOMER_ID, $customerId)
            ->addFieldToFilter(PreorderInterface::IS_AVAILABLE, \Fecon\Shipping\Model\Preorder::AVAILABLE)
            ->addFieldToFilter(PreorderInterface::QUOTE_ID, $quoteId)
            ->getLastItem()
            ->getId();
        try {
            $preorder = $this->preorderRepository->getById($preorderId);
        } catch (\Exception $ex) {
            $preorder = null;
        }

        return $preorder;
    }

    public function getShippingDescription($preorder)
    {
        $shippingMethods = $this->serializer->unserialize($preorder->getData(PreorderInterface::SHIPPING_METHOD));
        $shippingTitles = [];
        $shippingDescription = null;
        if ($shippingMethods) {
            foreach ($shippingMethods as $shippingMethod) {
                if (isset(Options::SHIPPING_METHODS[$shippingMethod])) {
                    $shippingTitles[] = Options::SHIPPING_METHODS[$shippingMethod];
                }
            }
            $shippingDescription = implode(', ', $shippingTitles);
        }

        return $shippingDescription;
    }
}
