<?php

namespace Fecon\Shipping\Plugin\Quote\Address;

use Magento\Framework\Model\AbstractModel;
use Fecon\Shipping\Model\Config\ShippingAmount;
use Magento\Quote\Model\Quote\Address\RateResult\AbstractResult;

/**
 * Class Rate
 * @package Fecon\Shipping\Plugin\Quote\Address
 */
class Rate
{
    /**
     * @var ShippingAmount
     */
    protected $shippingAmount;

    /**
     * Rate constructor.
     * @param ShippingAmount $shippingAmount
     */
    public function __construct(
        \Fecon\Shipping\Model\Config\ShippingAmount $shippingAmount
    ) {
        $this->shippingAmount = $shippingAmount;
    }

    /**
     * @param $subject
     * @param $result
     * @param AbstractResult $rate
     * @return mixed
     */
    public function afterImportShippingRate($subject, $result, AbstractResult $rate) {
        if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            $result->setPrice(
                $this->calculatorShippingAmount($result->getPrice())
            );
        }

        return $result;
    }

    /**
     * Update Shipping Amount.
     * @param $shippingPrice
     * @return float|int|mixed
     */
    public function calculatorShippingAmount($shippingPrice) {
        $percentShippingAmountEnable = $this->shippingAmount->getShippingCostsEnable();
        if ($percentShippingAmountEnable) {
            $percentForShipping = $this->shippingAmount->getPercentForShippingCost();
            $shippingPrice = $shippingPrice && $percentForShipping ? $shippingPrice +
                (($shippingPrice * $percentForShipping) / 100) : $shippingPrice;
        }
        return $shippingPrice;
    }
}
