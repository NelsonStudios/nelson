<?php

namespace Serfe\FlatRateMinimumAmount\Helper;

/**
 * Helper to get module configurations
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class Helper extends \Magento\Framework\App\Helper\AbstractHelper
{
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
}