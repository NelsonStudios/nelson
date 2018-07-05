<?php

namespace DevPhase\Feeds\Helper;

/**
 * Widget value getter
 * Class FeconWidgetGetter
 */
class FeconWidgetGetter
{

    const CACHE_LIFETIME = 3600;    // Feed cache lifetime (new values will be fetched only when cache expires)

    protected $feconCache;

    public function __construct(\DevPhase\Feeds\Helper\FeconWidgetCache $feconCache)
    {
        $this->feconCache = $feconCache;
    }

    /**
     * Getter + Cache
     * @return
     */
    public function get()
    {
        $cid = get_called_class();
        // Try to load cache
        if ($cached_data = $this->feconCache->get($cid)) {
            // Return cache if exists
            return $cached_data;
        }
        // If there's no cache, load new values
        $data = $this->get_raw();
        // Store new values to cache
        $this->feconCache->set($cid, $data, time() + self::CACHE_LIFETIME);
        // Return new values
        return $data;
    }

    /**
     * Raw getter (overloaded)
     * @return array
     */
    public function get_raw()
    {
        return array();
    }

}