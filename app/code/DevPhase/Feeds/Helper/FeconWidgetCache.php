<?php

namespace DevPhase\Feeds\Helper;

/**
 * Class FeconWidgetCache
 * Simple file cache helper
 */
class FeconWidgetCache
{
    protected $socialCache;

    public function __construct(\DevPhase\Feeds\Model\Cache\Type $cache)
    {
        $this->socialCache = $cache;
    }

    /**
     * Loads data from file cache
     * @param string $cid - unique cache ID
     * @return mixed|null - cache value or null if value expired or doesn't exist
     */
    public function get($cid)
    {
//        $filename = self::cid_file_name($cid);
//        if (!is_file($filename)) {
//            return null;
//        }
//        $json = file_get_contents($filename);
//        if ($json === false) {
//            return null;
//        }
//        $cache = json_decode($json, true);
//        if ($cache['expires'] && $cache['expires'] < time()) {
//            self::clear($cid);
//            return null;
//        }
//        return $cache['data'];
        $json = $this->socialCache->load($cid);
        $cache = null;
        if ($json) {
            $cache = json_decode($json, true);
        }

        return $cache;
    }

    /**
     * Saves data to file cache
     * @param string $cid - unique cache ID
     * @param mixed $data - cache data string or array
     * @param int|null $expires - cache expiration timestamp (null for permanent cache)
     * @return bool - true = success, false = failure
     */
    public function set($cid, $data, $expires = null)
    {
//        $filename = self::cid_file_name($cid);
//        $cache = array(
//            'expires' => $expires,
//            'data' => $data,
//        );
//        $json = json_encode($cache);
//        if (file_put_contents($filename, $json) === false) {
//            return false;
//        }
//        return true;
        $json = json_encode($data);
        return $this->socialCache->save($json, $cid);
    }

    /**
     * Clears cache (removes file)
     * @param string $cid - unique cache ID
     * @return bool
     */
    public static function clear($cid)
    {
        $filename = self::cid_file_name($cid);
        return unlink($filename);
    }

    /**
     * Generates cache JSON filename for given CID
     * @param string $cid - unique cache ID
     * @return string - filename
     */
    public static function cid_file_name($cid)
    {
        $cid = self::cid_check_plain($cid);
        return 'cache-' . $cid . '.json';
    }

    /**
     * Cleans CID from any uncommon chars
     * @param string $cid - unique cache ID
     * @return string
     */
    public static function cid_check_plain($cid)
    {
        $cid = strtolower($cid);
        $cid = preg_replace('#[^a-z0-9_-]#si', '', $cid);
        return $cid;
    }

}