<?php
 
namespace Fecon\ExternalCart\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PROTOCOL = 'externalcart/active_display/protocol';
    const HOSTNAME = 'externalcart/active_display/hostname';
    const PORT = 'externalcart/active_display/port';

    /**
     * $authorize 
     * 
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorize;
    /**
     * $jsonHelper
     * 
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Constructor
     * 
     * @param \Magento\Framework\AuthorizationInterface          $authorize
     * @param \Magento\Framework\Json\Helper\Data                $jsonHelper
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\AuthorizationInterface $authorize,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->authorize = $authorize;
        $this->jsonHelper = $jsonHelper;
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
     * checkAllowed function used to check for a valid access token.
     * @throws \Exception Authorization required. message as output.
     */
    public function checkAllowed() {
        if($this->authorize->isAllowed('Fecon_ExternalCart::cart') === false) {
            throw new \Exception(
                __('Authorization required.')
            );
        }
    }
    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '') {
        return $this->jsonHelper->jsonEncode($response);
    }
    /**
     * jsonDecode return a decoded json string to return a 
     * 
     * ResultInterface|ResponseInterface
     * Note the second parameter.
     * 
     * @param  string $strToDecode json string to decode
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function jsonDecode($strToDecode = '') {
        return $this->jsonHelper->jsonDecode($strToDecode, 1);
    }

    /**
     * makeCurlRequest
     * 
     * @param  string $origin           
     * @param  string $endpointPath     
     * @param  string $loggedInUserToken
     * @return string $response                   
     */
    public function makeCurlRequest($origin, $endpointPath, $loggedInUserToken) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $origin . $endpointPath,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "authorization: Bearer " . $loggedInUserToken, //This is the logged-in user Bearer do not confuse with access token.
            "cache-control: no-cache"
          ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
          return "cURL Error #:" . $err;
        } else {
          return $response;
        }
    }
}