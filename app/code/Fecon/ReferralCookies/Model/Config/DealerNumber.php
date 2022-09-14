<?php

declare(strict_types=1);

namespace Fecon\ReferralCookies\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class DealerNumber
{
    const XML_PATH_REFERNAL_COOKIE_ENABLE = 'fecon_cookies/configuration_cookies/enable';
    const XML_PATH_REFERNAL_COOKIE_TIME = 'fecon_cookies/configuration_cookies/time';

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
     * Get Referral Cookies enable.
     *
     * @return mixed
     */
    public function getReferralCookiesEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REFERNAL_COOKIE_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES
        );
    }

    /**
     * Get Referral Cookies content.
     *
     * @return mixed
     */
    public function getReferralCookiesTime()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REFERNAL_COOKIE_TIME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES
        );
    }
}
