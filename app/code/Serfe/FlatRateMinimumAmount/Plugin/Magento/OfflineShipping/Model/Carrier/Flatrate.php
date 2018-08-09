<?php

namespace Serfe\FlatRateMinimumAmount\Plugin\Magento\OfflineShipping\Model\Carrier;

/**
 * Flatrate Plugin
 *
 * 
 */
class Flatrate
{
    /**
     * Checkout Session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Configuration Helper
     *
     * @var \Serfe\FlatRateMinimumAmount\Helper\Config
     */
    protected $configHelper;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Serfe\FlatRateMinimumAmount\Helper\Config $configHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->configHelper = $configHelper;
    }

    /**
     * Plugin to show/hide Flat Rate Shipping based on current cart subtotal
     *
     * @param \Magento\OfflineShipping\Model\Carrier\Flatrate $subject
     * @param \Closure $proceed Comment
     * @param string $field
     * @return boolean
     */
    public function aroundGetConfigFlag(
        \Magento\OfflineShipping\Model\Carrier\Flatrate $subject,
        \Closure $proceed,
        $field
    ) {
        $returnValue = $proceed($field);

        if ($field == 'active') {
            $returnValue = $this->validateFlatRateShipping($returnValue);
        }

        return $returnValue;
    }

    /**
     * Validate if Flat Rate Shipping can be used for checkout
     *
     * @param boolean $currentValue
     * @return boolean
     */
    protected function validateFlatRateShipping($currentValue)
    {
        $minimumAmount = $this->configHelper->getFlatRateMinAmount();
        $quote = $this->checkoutSession->getQuote();
        $subtotal = (int) $quote->getSubtotal();
        $returnValue = $currentValue;
        if ($subtotal < $minimumAmount) {
            $returnValue = false;
        }

        return $returnValue;
    }
}
