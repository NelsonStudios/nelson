<?php

namespace Serfe\FlatRateMinimumAmount\Plugin\Magento\OfflineShipping\Model\Carrier;

/**
 * Flatrate Plugin
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class Flatrate
{
    protected $checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }


    /**
     * Undocumented function
     *
     * @param \Magento\OfflineShipping\Model\Carrier\Flatrate $subject
     * @param \Closure $proceed Comment
     * @param [type] $field
     * @return void
     */
    public function aroundGetConfigFlag(
        \Magento\OfflineShipping\Model\Carrier\Flatrate $subject,
        \Closure $proceed,
        $field
    ) {
        $returnValue = $proceed($field);
        // var_dump($returnValue);die();
        if ($field == 'active') {
            $quote = $this->checkoutSession;
            $subtotal = $quote->getSubtotal();
            if ($subtotal < 3000) {
                $returnValue = false;
            }

            // var_dump($subtotal, $returnValue);die();
        }

        return $returnValue;
    }
}
