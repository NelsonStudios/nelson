<?php

namespace Serfe\Shipping\Observer\Payment;

/**
 * Description of MethodIsActive
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class MethodIsActive implements \Magento\Framework\Event\ObserverInterface
{

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
        if (null !== $quote && $quote->getShippingAddress() && strpos($quote->getShippingAddress()->getShippingMethod(), 'manualshipping') !== false) {
            $result->setData('is_available', false);
        }
    }
}