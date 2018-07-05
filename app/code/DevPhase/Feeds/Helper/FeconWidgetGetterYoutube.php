<?php

namespace DevPhase\Feeds\Helper;

/**
 * Feed getter for Youtube Videos
 * Youtube API v3
 * Class FeconWidgetGetterYoutube
 */
class FeconWidgetGetterYoutube extends FeconWidgetGetter
{

    const API_URL = 'https://www.googleapis.com/youtube/v3/';
    const API_KEY = 'AIzaSyBCgwCbGlJIAQzF1qvZ3MwXcClken2-HdQ';
    const CHANNEL = 'FeconInc'; // Channel name (can be obtained from Channel URL)

    /**
     * Raw getter
     * @return array
     */
    public function get_raw()
    {
        // Getting channel ID for next request
        $channel_id = static::channel_id_get(static::CHANNEL);
        // Search all videos by Channel ID
        $resp = static::api_request('search', array(
                'part' => 'snippet',
                'channelId' => $channel_id,
                'order' => 'date',
                'maxResults' => 12,
        ));
        $ret = array();
        if ($resp) {
            if (isset($resp['items'])) {
                foreach ($resp['items'] as $item) {
                    $r = array();
                    $r['id'] = $item['id']['videoId'];
                    $r['link'] = 'https://www.youtube.com/watch?v=' . $r['id'];
                    $r['title'] = $item['snippet']['title'];
                    $r['description'] = $item['snippet']['description'];
                    $r['image'] = $item['snippet']['thumbnails']['high']['url'];
                    $ret[] = $r;
                }
            }
        }
        return $ret;
    }

    /**
     * Get Channel ID by channel name
     * @param string $channel
     * @return strign|null
     */
    public static function channel_id_get($channel)
    {
        $resp = static::api_request('channels', array(
                'part' => 'id',
                'forUsername' => static::CHANNEL,
        ));
        $id = null;
        if ($resp) {
            if (isset($resp['items'])) {
                if (count($resp['items'])) {
                    $id = @$resp['items'][0]['id'];
                }
            }
        }
        return $id;
    }

    /**
     * Performs YT API request
     * @param string $endpoint
     * @param array $params
     * @return array|null
     */
    public static function api_request($endpoint, $params = array())
    {
        $params['key'] = static::API_KEY;
        $url = self::API_URL . $endpoint . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        if ($response === false) {
            return null;
        }
        $response = json_decode($response, true);
        return $response;
    }

}