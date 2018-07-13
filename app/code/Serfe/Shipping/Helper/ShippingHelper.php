<?php

namespace Serfe\Shipping\Helper;

/**
 * Helper to get shipping options
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class ShippingHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Serfe\Shipping\Helper\PreorderHelper 
     */
    protected $preorderHelper;
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Serfe\Shipping\Helper\PreorderHelper $preorderHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Serfe\Shipping\Helper\PreorderHelper $preorderHelper
    ) {
        $this->preorderHelper = $preorderHelper;
        parent::__construct($context);
    }

    /**
     * Get shipping price
     *
     * @param string $shippingCode
     * @return string|float
     */
    public function getShippingPrice($shippingCode)
    {
        $price = '0.00';
        $preorder = $this->preorderHelper->getPreorderByShippingCode($shippingCode);
        if ($preorder) {
            $price = $preorder->getShippingPrice();
        }

        return $price;
    }

    /**
     * Check if payment is available for $shippingCode and current customer
     *
     * @param string $shippingCode
     * @return boolean
     */
    public function isPaymentAvailable($shippingCode)
    {
        $isPaymentAvailable = false;
        $preorder = $this->preorderHelper->getPreorderByShippingCode($shippingCode);
        if ($preorder) {
            $isPaymentAvailable = $preorder->getIsAvailable();
        }

        return $isPaymentAvailable;
    }
}