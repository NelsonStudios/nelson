<?php

namespace Fecon\SytelineIntegration\Model\Cache;

/**
 * Custom cache to save API Request results
 */
class Type extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{

    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'syteline_cache';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'SYTELINE_CACHE';

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(\Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}