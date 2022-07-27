<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Store\Model\ScopeInterface;

class CarrierOnline
{
    /**
     * @var bool
     */
    private $isEnabled;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->isEnabled = $scopeConfig->isSetFlag(
            'DimensionalShipping/shipping_rates/separate_requests', ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param AbstractCarrierOnline $subject
     * @param \Closure $proceed
     * @param $field
     * @return mixed
     */
    public function aroundGetConfigData(
        AbstractCarrierOnline $subject,
        \Closure $proceed,
        $field
    ) {
        if ($field === 'shipment_requesttype') {
            if ($subject->getCarrierCode() == 'fedex') {
                return false;
            } elseif ($this->isEnabled && in_array($subject->getCarrierCode(), ['ups', 'usps', 'dhl'])) {
                return true;
            }
        }
        return $proceed($field);
    }
}