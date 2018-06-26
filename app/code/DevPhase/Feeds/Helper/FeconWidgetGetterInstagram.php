<?php

namespace DevPhase\Feeds\Helper;

/**
 * Instagram Feed getter
 * Gets User posts
 * Class FeconWidgetGetterInstagram
 */
class FeconWidgetGetterInstagram extends FeconWidgetGetter
{

    const CLIENT_ID = '547f12fb04e2442695f77673570b854e';
    const CLIENT_SECRET = '56a09f658162423fb0c0a92bbb47c69f';
    const REDIRECT = 'https://fecon.com/widget/instagram-auth.php';

    protected $scopeConfig;

    public function __construct(FeconWidgetCache $feconCache, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;

        parent::__construct($feconCache);
    }

    /**
     * Raw getter
     * @return array
     * @throws \Andreyco\Instagram\Exception\InvalidParameterException
     */
    public function get_raw()
    {
        $ret = array();

        // Getter class
        $instagram = static::instagram();
        // Load Access token from cache
//        $cid = get_called_class() . '-accesstoken';
//        $access_token = FeconWidgetCache::get($cid);
        $access_token = $this->scopeConfig->getValue('social_config/setting/instagram_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        // If we have access token
        if ($access_token) {
            // Use it
            $instagram->setAccessToken($access_token);
            // Load User posts
            $data = $instagram->getUserMedia('self', 20);
            foreach ($data->data as $item) {
                $r = array();
                $r['id'] = $item->id;
                $r['link'] = $item->link;
                $r['title'] = '';
                $r['description'] = $item->caption ? $item->caption->text : '';
                $r['image'] = $item->images->standard_resolution->url;
                $ret[] = $r;
            }
        }

        return $ret;
    }

    /**
     * Construct Instagram getter Class
     * @return \Andreyco\Instagram\Client
     * @throws \Andreyco\Instagram\Exception\InvalidParameterException
     */
    public static function instagram()
    {
        $instagram = new \Andreyco\Instagram\Client(array(
            'apiKey' => self::CLIENT_ID,
            'apiSecret' => self::CLIENT_SECRET,
            'apiCallback' => self::REDIRECT,
            'scope' => array('basic', 'public_content'),
        ));
        return $instagram;
    }

    /**
     * Simple Auth functionality.
     * Redirects user to Instagram Auth page and retrieves access token
     * Should be called on self::REDIRECT page
     * @return bool
     * @throws \Andreyco\Instagram\Exception\InvalidParameterException
     */
    public static function auth()
    {
        // Get getter class
        $instagram = static::instagram();
        // Check Code in URL. It will be available after acces will be granted
        $code = @$_GET['code'];
        // If no code (page opened first time)
        if (!$code) {
            // Redirect to Instagram Auth page
            header('Location: ' . $instagram->getLoginUrl());
            exit();
        }
        // If we have code
        // Exchange Code to Access Token
        $data = $instagram->getOAuthToken($code);
        // if error occurred
        if (@$data->error_message) {
            // print it
            echo $data->error_message;
        }
        // If we got Access Token
        if (@$data->access_token) {
            // Save it to permanent cache
            $cid = get_called_class() . '-accesstoken';
            FeconWidgetCache::set($cid, $data->access_token, null);
            // OK
            return true;
        }
        // No Access Token
        return false;
    }

}