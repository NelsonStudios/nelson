<?php

declare(strict_types=1);

namespace Fecon\Shipping\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ShippingAmount
{
    const XML_PATH_ADD_SHIPPING_COST_ENABLE = 'fecon_shipping/configuration_shipping/enable';
    const XML_PATH_ADD_SHIPPING_COST_VALUE = 'fecon_shipping/configuration_shipping/shipping_amount';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Shipping constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get Shipping Cost enable.
     *
     * @return mixed
     */
    public function getShippingCostsEnable()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADD_SHIPPING_COST_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * Get Percent For Shipping Cost.
     *
     * @return mixed
     */
    public function getPercentForShippingCost()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADD_SHIPPING_COST_VALUE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES
        );
    }
}
