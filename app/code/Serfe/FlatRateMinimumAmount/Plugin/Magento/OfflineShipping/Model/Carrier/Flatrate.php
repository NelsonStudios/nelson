<?php


namespace Serfe\FlatRateMinimumAmount\Plugin\Magento\OfflineShipping\Model\Carrier;

class Flatrate
{
    protected $checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function aroundGetConfigFlag(
        \Magento\OfflineShipping\Model\Carrier\Flatrate $subject,
        \Closure $proceed,
        $field
    ) {
        $returnValue = $proceed($field);
        var_dump($returnValue);die();
        if ($field == 'active') {
            $quote = $this->checkoutSession;
            $subtotal = $quote->getSubtotal();
            if ($subtotal < 15) {
                $returnValue = true;
            }

            var_dump($subtotal, $returnValue);die();
        }

        return $returnValue;
    }
}
