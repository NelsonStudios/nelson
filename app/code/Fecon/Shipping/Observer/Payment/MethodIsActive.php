<?php

namespace Fecon\Shipping\Observer\Payment;

/**
 * Observer to check if payment is available
 */
class MethodIsActive implements \Magento\Framework\Event\ObserverInterface
{
    protected $shippingHelper;

    public function __construct(\Fecon\Shipping\Helper\ShippingHelper $shippingHelper)
    {
        $this->shippingHelper = $shippingHelper;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $result = $observer->getEvent()->getResult();
        $quote = $observer->getEvent()->getQuote();
        /* If shipping method is manually calculated disable all payment methods */
        if (null !== $quote && $quote->getShippingAddress())  {
            $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
            if (strpos($shippingMethod, 'manualshipping') !== false) {
                $isPaymentAvailable = $this->shippingHelper->isPaymentAvailable($shippingMethod);
                $result->setData('is_available', $isPaymentAvailable);
            }
        }
    }
}