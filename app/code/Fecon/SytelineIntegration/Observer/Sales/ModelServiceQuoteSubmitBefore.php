<?php

namespace Fecon\SytelineIntegration\Observer\Sales;

class ModelServiceQuoteSubmitBefore implements \Magento\Framework\Event\ObserverInterface
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
        $quote = $observer->getData('quote');
        $sytelineExtraFields = $quote->getSytelineCheckoutExtraFields();
        $order = $observer->getData('order');
        $order->setSytelineCheckoutExtraFields($sytelineExtraFields);
    }
}