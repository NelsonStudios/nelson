<?php

namespace Fecon\Shipping\Observer\Checkout;

/**
 * Class OrderSender
 * @package Fecon\Shipping\Observer\Checkout
 */
class OrderSender implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Update Order Confirmation email.
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getTransport();
        $order = $observer->getTransport()->getOrder();
        $extraFields = json_decode($order->getSytelineCheckoutExtraFields());
        $transport['purchaseOrderNumber'] = $extraFields->purchaseOrderNumber ?? '';
        $transport['companyName'] = $extraFields->companyName  ?? '';
        $transport['serialNumber'] = $extraFields->serialNumber ?? '';
    }
}
