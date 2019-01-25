<?php

namespace Fecon\RewardPoints\Helper;

/**
 * ConfigHelper class
 */
class ConfigHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    const MINIMUM_SILVER_PATH = 'aw_rewardpoints/calculation/silver_level';
    const MINIMUM_GOLD_PATH = 'aw_rewardpoints/calculation/gold_level';
    const MINIMUM_POINTS_PATH = 'aw_rewardpoints/notifications/points_for_admin_notification';
    const ADMIN_EMAIL_PATH = 'aw_rewardpoints/notifications/admin_email';

    /**
     * getMinimumSilverLevel
     *
     * @return int
     */
    public function getMinimumSilverLevel()
    {
        $minimumForLevel = (int) $this->scopeConfig->getValue(self::MINIMUM_SILVER_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return $minimumForLevel;
    }

    /**
     * getMinimumGoldLevel
     *
     * @return int
     */
    public function getMinimumGoldLevel()
    {
        $minimumForLevel = (int) $this->scopeConfig->getValue(self::MINIMUM_GOLD_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return $minimumForLevel;
    }

    /**
     * getMinimumForNotification
     *
     * @return string
     */
    public function getMinimumForNotification()
    {
        $minimumForNotification = $this->scopeConfig->getValue(self::MINIMUM_POINTS_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return $minimumForNotification;
    }

    /**
     * getAdminEmail
     *
     * @return string
     */
    public function getAdminEmail()
    {
        $minimumForLevel = $this->scopeConfig->getValue(self::ADMIN_EMAIL_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        return $minimumForLevel;
    }
}