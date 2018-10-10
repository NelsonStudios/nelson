<?php

namespace Fecon\Shipping\Observer\Checkout;

/**
 * Observer that listen to the "submit order successfully" event
 */
class Submit implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Preorder Helper
     *
     * @var \Fecon\Shipping\Helper\PreorderHelper
     */
    protected $preorderHelper;

    /**
     * Constructor
     *
     * @param \Fecon\Shipping\Helper\PreorderHelper $preorderHelper
     * @return void
     */
    public function __construct(\Fecon\Shipping\Helper\PreorderHelper $preorderHelper)
    {
        $this->preorderHelper = $preorderHelper;
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
        $order = $observer->getOrder();
        if(method_exists($order,'getShippingMethod')) {
            $shippingMethod = $order->getShippingMethod();
            if (strpos($shippingMethod, 'manualshipping') !== false) {
                $quote = $observer->getQuote();
                $this->preorderHelper->completePreorder($quote);
            }
        }
    }
}
