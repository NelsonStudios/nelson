<?php

namespace Serfe\Shipping\Helper;

/**
 * Helper to create PreorderHelper from quote's data
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class PreorderHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $customerSession;
    
    protected $preorderFactory;

    protected $preorderRepository;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Serfe\Shipping\Model\PreorderFactory $preorderFactory,
        \Serfe\Shipping\Api\PreorderRepositoryInterface $preorderRepository
    ) {
        $this->customerSession = $customerSession;
        $this->preorderFactory = $preorderFactory;
        $this->preorderRepository = $preorderRepository;

        parent::__construct($context);
    }

    public function createPreorder(\Magento\Quote\Model\Quote $quote)
    {
        $preorderData = $this->getPreorderData($quote);
        $preorder = $this->preorderFactory->create();
        $preorder->addData($preorderData);
        
        try {
            $created = $this->preorderRepository->save($preorder);
        } catch (\Exception $exc) {
//            echo $exc->getTraceAsString();
            $this->_logger->error($exc->getMessage());
            $created = false;
        }
        
        return $created;
    }

    protected function getPreorderData(\Magento\Quote\Model\Quote $quote)
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $quoteId = $quote->getId();
        $addressId = $quote->getShippingAddress()->getId();
        
        
        $preorderData = [
            \Serfe\Shipping\Api\Data\PreorderInterface::IS_AVAILABLE => false,
            \Serfe\Shipping\Api\Data\PreorderInterface::CUSTOMER_ID => $customerId,
            \Serfe\Shipping\Api\Data\PreorderInterface::SHIPPING_METHOD => $shippingMethod,
            \Serfe\Shipping\Api\Data\PreorderInterface::QUOTE_ID => $quoteId,
            \Serfe\Shipping\Api\Data\PreorderInterface::ADDRESS_ID => $addressId
        ];
        
        return $preorderData;
    }
}