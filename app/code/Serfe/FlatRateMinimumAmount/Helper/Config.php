<?php

namespace Serfe\FlatRateMinimumAmount\Helper;

/**
 * Helper to get module configurations
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    const FLAT_RATE_MIN_AMOUNT = 'serfe_flat_rate/settings/minimum_amount';

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeMaganger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeMaganger
    ) {
        $this->storeManager = $storeMaganger;
        
        parent::__construct($context);
    }

    /**
     * Get configured value for current website
     *
     * @param string $configPath
     * @return mixed
     */
    protected function getConfigValue($configPath)
    {
        $websiteCode = $this->storeManager->getWebsite()->getCode();
        return $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $websiteCode);
    }

    /**
     * Get Flat Rate Minimum Amount
     *
     * @return int
     */
    public function getFlatRateMinAmount()
    {
        $minAmount = (int) $this->getConfigValue($this::FLAT_RATE_MIN_AMOUNT);

        return $minAmount;
    }
}