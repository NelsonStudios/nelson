<?php

namespace DevPhase\Feeds\Model\Cache;

/**
 * Custom Cache Type
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class Type extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    const TYPE_IDENTIFIER = 'social_feeds';
    const CACHE_TAG = 'SOCIALFEEDS';

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }

}