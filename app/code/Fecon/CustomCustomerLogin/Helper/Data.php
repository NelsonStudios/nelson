<?php
 
namespace Fecon\CustomCustomerLogin\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PROTOCOL = 'externalcart/active_display/protocol';
    const HOSTNAME = 'externalcart/active_display/hostname';
    const PORT = 'externalcart/active_display/port';
    const TEK = 'externalcart/active_display/tek';

    /**
     * $tek Tenant Encrypted Token
     * @var string
     */
    protected $tek;
    protected $origin;

    /**
     * Constructor
     * 
     * @param \Magento\Framework\AuthorizationInterface          $authorize
     * @param \Magento\Framework\Json\Helper\Data                $jsonHelper
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->origin = 'https://documoto.digabit.com/ui/home';
        $this->tek = 'Nyai7LntekMPvQCgjzGK%2BJ0gMeg'; // We should get this from admin side as a config.
        parent::__construct($context);
    }
    /**
     * protocol
     * 
     * @return string protocol config value
     */
    public function protocol()
    {
        return $this->scopeConfig->getValue(
            self::PROTOCOL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * hostname
     * 
     * @return string hostname config value
     */
    public function hostname()
    {
        return $this->scopeConfig->getValue(
            self::HOSTNAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * port 
     * 
     * @return string port config value
     */
    public function port()
    {
        return $this->scopeConfig->getValue(
            self::PORT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * port 
     * 
     * @return string port config value
     */
    public function tek()
    {
        return $this->scopeConfig->getValue(
            self::TEK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * [checkDocumotoUser description]
     * @return [type] [description]
     */
    public function checkDocumotoUser($customer) {
        // Get customer email
        $customerEmail = $customer->getEmail();
        // return $this->get_redirect_final_target($this->origin . '?tek=' . $this->tek . '&username=' . urlencode($customerEmail));
        return $this->getWebPage($this->origin . '?tek=' . $this->tek . '&username=' . urlencode($customerEmail), 'trackAllLocations');
        // $curl = curl_init();
        // curl_setopt_array($curl, array(
        //   CURLOPT_URL => $this->origin . '?tek=' . $this->tek . '&username=' . $customerEmail,
        //   CURLOPT_RETURNTRANSFER => true,
        //   CURLOPT_HEADER => true,
        //   CURLOPT_VERBOSE => true,
        //   CURLOPT_ENCODING => "",
        //   CURLOPT_MAXREDIRS => 10,
        //   CURLOPT_TIMEOUT => 30,
        //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //   CURLOPT_CUSTOMREQUEST => 'GET',
        //   CURLOPT_HTTPHEADER => array(
        //     "cache-control: no-cache"
        //   ),
        // ));
        // $response = curl_exec($curl);
        // $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        // $header = substr($response, 0, $header_size);
        // $body = substr($response, $header_size);
        // echo '<pre>';
        // var_dump($header);
        // echo '</pre>';
        // exit;
        // $err = curl_error($curl);
        // curl_close($curl);

        // if ($err) {
        //   return "cURL Error #:" . $err;
        // } else {
        //   return $response;
        // }
    }
    // FOLLOW A SINGLE REDIRECT:
    // This makes a single request and reads the "Location" header to determine the
    // destination. It doesn't check if that location is valid or not.
    public function get_redirect_target($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = curl_exec($ch);
        curl_close($ch);
        // Check if there's a Location: header (redirect)
        if (preg_match('/^Location: (.+)$/im', $headers, $matches))
            return trim($matches[1]);
        // If not, there was no redirect so return the original URL
        // (Alternatively change this to return false)
        return $url;
    }
    // FOLLOW ALL REDIRECTS:
    // This makes multiple requests, following each redirect until it reaches the
    // final destination.
    public function get_redirect_final_target($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // follow redirects
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // set referer on redirect
        curl_exec($ch);
        $target = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        if ($target)
            return $target;
        return false;
    }

    public function getWebPage($url, $redirectcallback = null){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1) Gecko/20061024 BonEcho/2.0");

    $html = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
        list($httpheader) = explode("\r\n\r\n", $html, 2);
        $matches = array();
        preg_match('/(Location:|URI:)(.*?)\n/', $httpheader, $matches);
        $nurl = trim(array_pop($matches));
        $url_parsed = parse_url($nurl);
        if (isset($url_parsed)) {
            if($redirectcallback){ // callback
                 $redirectcallback($nurl, $url);
            }
            $html = getWebPage($nurl, $redirectcallback);
        }
    }
    return $html;
}

public function trackAllLocations($newUrl, $currentUrl){
    return $currentUrl.' ---> '.$newUrl."\r\n";
}
    /**
     * [loginDocumotoUserSSO description]
     * @return [type] [description]
     */
    public function loginDocumotoUserSSO() {

    }
    /**
     * [redirectToDocumotoSite description]
     * @return [type] [description]
     */
    public function redirectToDocumotoSite() {

    }
    /**
     * [redirectToDocumotoNoAccountPage description]
     * @return [type] [description]
     */
    public function redirectToDocumotoNoAccountPage() {

    }

}