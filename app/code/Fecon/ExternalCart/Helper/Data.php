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
     * $customerToken
     * 
     * @var string
     */
    protected $customerToken;

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
     * This function is intended to perform curl requests when needed.
     * 
     * @param  string $origin The origin with protocol + domain + port
     * @param  string $endpointPath The endpoint path when the request need to be performed.
     * @param  string $loggedInUserToken The user token if there's a logged-in customer or the Authorization access-token.
     * @param  string $type The method type like "POST", "GET", etc
     * @return string $response The response result from curl request or error message.               
     */
    public function makeCurlRequest($origin, $endpointPath, $loggedInUserToken, $type) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $origin . $endpointPath,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => $type,
          CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer " . $loggedInUserToken, //This is the logged-in user Bearer do not confuse with access token.
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
    /**
     * checkUserTypeAndSetEndpoints
     * 
     * @param  boolean $isCustomer in order to check if settings need to be prepared for guest or customer user type.
     * @return array $settings settings to be used in SOAP requests.
     */
    public function checkUserTypeAndSetEndpoints($isCustomer = false) {
        $settings = [
            'endpointsPaths' => [],
            'opts' => [],
            'customer' => $isCustomer
        ];
        /**
         * Get wsdl endpoint names based on guest or non-guest customers.
         */
        $settings['endpointsPaths']['quoteCartManagementV1']     = (($isCustomer)? 'quoteCartManagementV1' : 'quoteGuestCartManagementV1');
        $settings['endpointsPaths']['quoteCartRepositoryV1']     = (($isCustomer)? 'quoteCartRepositoryV1' : 'quoteGuestCartRepositoryV1');
        $settings['endpointsPaths']['quoteCartItemRepositoryV1'] = (($isCustomer)? 'quoteCartItemRepositoryV1' : 'quoteGuestCartItemRepositoryV1');
        /**
         * Dinamically get the methods names to call based on guest or non-guest customers.
         */
        $settings['endpointsPaths']['quoteCartManagementV1Endpoint'] = $settings['endpointsPaths']['quoteCartManagementV1'] . (($isCustomer)? 'GetCartForCustomer' : 'CreateEmptyCart');
        $settings['endpointsPaths']['quoteCartRepositoryV1Get']      = $settings['endpointsPaths']['quoteCartRepositoryV1'] . 'Get';
        $settings['endpointsPaths']['quoteCartItemRepositoryV1Save'] = $settings['endpointsPaths']['quoteCartItemRepositoryV1'] . 'Save';

        if($isCustomer) {
            //TODO: remove harcoded access token (make a setting)
            $settings['opts']['stream_context'] = stream_context_create([
                'http' => [
                    'header' => sprintf('Authorization: Bearer %s', 'j2u1n6bqmtj6w0kfqf3m25m33qv1e8km')
                ]
            ]);
        }
        return $settings;
    }
}