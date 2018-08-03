<?php
namespace Fecon\ExternalCart\Model;

use Fecon\ExternalCart\Api\CartInterface;
 
/**
 * Defines the implementaiton class of the CartInterface
 */
class Cart implements CartInterface {
    /**
     * $quoteCartManagementV1
     * @var string
     */
    protected $quoteCartManagementV1;
    /**
     * $quoteCartRepositoryV1
     * @var string
     */
    protected $quoteCartRepositoryV1;
    /**
     * $quoteCartItemRepositoryV1
     * @var string
     */
    protected $quoteCartItemRepositoryV1;
    /**
     * $quoteCartManagementV1CreateEmptyCart
     * @var string
     */
    protected $quoteCartManagementV1CreateEmptyCart;
    /**
     * $quoteCartRepositoryV1Get
     * @var string
     */
    protected $quoteCartRepositoryV1Get;
    /**
     * $quoteCartItemRepositoryV1Save
     * @var string
     */
    protected $quoteCartItemRepositoryV1Save;
    /**
     * $customerLoggedIn
     * @var mixed integer/boolean
     */
    protected $customerLoggedIn = false;
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
     * $coreSession
     * 
     * @var \Magento\Framework\Session\SessionManagerInterface 
     */
    protected $coreSession;
    /**
     * $customerSession
     * 
     * @var \Magento\Customer\Model\Session 
     */
    protected $customerSession;
    /**
     * $request
     * 
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * $externalCartHelper
     * 
     * @var \Fecon\ExternalCart\Helper\Data 
     */
    protected $externalCartHelper;
    /**
     * $protocol
     * 
     * @var string
     */
    protected $protocol;
    /**
     * $hostname
     * 
     * @var string
     */
    protected $hostname;
    /**
     * $port 
     * 
     * @var string
     */
    protected $port;
    /**
     * The "full domain" with protocol + domain + port
     * @var string
     */
    public $origin;

    /**
     * Constructor
     * 
     * @param \Magento\Framework\AuthorizationInterface          $authorize         
     * @param \Magento\Framework\Json\Helper\Data                $jsonHelper        
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession       
     * @param \Magento\Customer\Model\SessionFactory             $customerSession   
     * @param \Magento\Framework\App\Request\Http                $request           
     * @param \Fecon\ExternalCart\Helper\Data                    $externalCartHelper
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorize,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Fecon\ExternalCart\Helper\Data $externalCartHelper
    ) {
        $this->authorize = $authorize;

        /**
         * First check if it's allowed to use the API.
         */
        $this->_checkAllowed();

        $this->jsonHelper = $jsonHelper;
        $this->coreSession = $coreSession;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->externalCartHelper = $externalCartHelper;

        $this->protocol = $this->externalCartHelper->protocol();
        $this->hostname = $this->externalCartHelper->hostname();
        $this->port = $this->externalCartHelper->port();

        if(!empty($this->protocol) && !empty($this->hostname)) {
            $this->origin = $this->protocol . $this->hostname;
        }
        if(!empty($this->port)) {
            $this->origin .= ':' . $this->port;
        }
        /* Add backend settings validation */
        if(empty($this->origin)) {
            throw new \Exception(
                __('Please check External Cart Settings in Admin section.')
            );
        }
        /**
         * Get customer id or false otherwise.
         */
        $this->customerLoggedIn = $this->getLoggedinCustomerId();
        /**
         * Get wsdl endpoint names based on guest or non-guest customers.
         */
        $this->quoteCartManagementV1     = (($this->customerLoggedIn)? 'quoteCartManagementV1' : 'quoteGuestCartManagementV1');
        $this->quoteCartRepositoryV1     = (($this->customerLoggedIn)? 'quoteCartRepositoryV1' : 'quoteGuestCartRepositoryV1');
        $this->quoteCartItemRepositoryV1 = (($this->customerLoggedIn)? 'quoteCartItemRepositoryV1' : 'quoteGuestCartItemRepositoryV1');
        /**
         * Dinamically get the methods names to call based on guest or non-guest customers.
         */
        $this->quoteCartManagementV1CreateEmptyCart = $this->quoteCartManagementV1 . 'CreateEmptyCart';
        $this->quoteCartRepositoryV1Get             = $this->quoteCartRepositoryV1 . 'Get';
        $this->quoteCartItemRepositoryV1Save        = $this->quoteCartItemRepositoryV1 . 'Save';
    }
    /**
     * Create and get new token of the created guest cart
     *
     * @api
     * @return string $token of created guest cart.
     * @throws \SoapFault response
     */
    public function createCartToken() {
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->quoteCartManagementV1);
        try {
            $token = $client->{$this->quoteCartManagementV1CreateEmptyCart}();
            /* Store cartId token in session temporary */
            $this->setCartToken($token->result);
            return $token->result; //token id of recently created cart.
        } catch(\SoapFault $e) {
            return $e->getMessage();
        }
    }
    /**
     * Set the token of the recently created guest cart
     *
     * @api
     * @return void
     */
    public function setCartToken($cartId) {
        $this->coreSession->start();
        $this->coreSession->setCartId($cartId);
    }
    /**
     * Get the token of the recently created guest cart
     *
     * @api
     * @return string $token of created guest cart or empty array otherwise.
     */
    public function getCartToken() {
        $this->coreSession->start();
        return $this->coreSession->getCartId();
    }
    /**
     * Get the cart information.
     * 
     * @api
     * @return stdObject $cartInfo The cart information as an object.
     * @throws \SoapFault response
     */
    public function getCartInfo($cartId) {
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->quoteCartRepositoryV1);
        try {
            $cartInfo = $client->{$this->quoteCartRepositoryV1Get}(array('cartId' => $cartId));
            return $this->jsonResponse($cartInfo->result); //Return cartInfo result object with cart information.
        } catch(\SoapFault $e) {
            return $e->getMessage();
        }
    }
    /**
     * Function to add products into guest cart.
     * 
     * @api
     * @param  string $cartId created guest cart id.
     * @param  array  $cartItem array with product data
     * @return integer $product related information on success otherwise throws SoapFault
     * @throws \SoapFault response
     */
    public function addProductIntoCart() {
        $postData = $this->request->getPost();
        return $this->_addProduct($postData);
    }
    /**
     * Function to get the cart url to access to the guest cart (with previously generated token).
     * 
     * @api
     * @return string $url The cart url
     */
    public function getCartUrl() {
        $cartId = $this->getCartToken();
        if(!empty($cartId)) {
            return $this->origin . '/externalcart/cart/?cartId=' . $this->getCartToken();
        }
        return false;
    }
    /**
     * Function to add products into cart but also:
     *  - Generate token if not exists
     *  - If token exists only add to cart in the current sessioned cart.
     *
     * This function will allow you to add to cart without previously generate a token, 
     * so the token it's automatically generated and then retrieved from session if exist
     * for next "add-to-cart" actions the user perform.
     *
     * This function doesn't require you to send "cartId" or "quoteId" params.
     *
     * Example data: 
     *   {
     *     "cartItem":
     *     {
     *       "sku": "BH-080",
     *       "qty":1
     *     }
     *   }
     * 
     * @api
     * @param cartId The cart id where you want to store the items (optional, if is not sent, a new will be created and used in next requests)
     * @param body The body (json object) post data with items you want to store. (quoteId is optional)
     * @return \Fecon\ExternalCart\Api\CartInterface $productAdded object with product related information.
     * @throws \SoapFault response
     */
    public function addToCart() {
        /* Get post data */
        $postData = $this->request->getPost();
        $cartIdFromSession = $this->getCartToken();
        /* If postData['cartId'] is not there but in session */
        if(empty($postData['cartId']) && !empty($cartIdFromSession)) {
            $postData['cartId'] = $cartIdFromSession;
        } else if(empty($postData['cartId']) && empty($cartIdFromSession)) { // Create new cartId
            $postData['cartId'] = $this->createCartToken();
        }
        /* Then add product */
        return $this->_addProduct($postData, true);
    }
    /**
     * Function to add product into cart using Magneto 2 REST API (with SOAP in this case)
     * 
     * @api
     * @param $postData array with product data.
     * @return integer $productId on success otherwise throws SoapFault
     * @throws \SoapFault response
     */
    private function _addProduct($postData, $updateCartId = false) {
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->quoteCartItemRepositoryV1);
        $cartItemData = $this->jsonHelper->jsonDecode($postData['body'], 1);
        if($updateCartId) {
            /* Update cartId in body post data */
            $cartItemData['cartItem']['quoteId'] = $postData['cartId'];
        }
        $productData = [
            'cartId' => $postData['cartId'], 
            'cartItem' => $cartItemData['cartItem']
        ];
        try {
            $productAdded = $client->{$this->quoteCartItemRepositoryV1Save}($productData);
            if($updateCartId) {
                $productAdded->result->cartId = $postData['cartId'];
            }
            return $this->jsonResponse($productAdded->result); //Return cartInfo result object with cart information.
        } catch(\SoapFault $e) {
            return $e->getMessage() . nl2br("\n Are you sure this is the correct cartId?", false);
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
     * [getLoggedinCustomerId description]
     * @return [type] [description]
     */
    public function getLoggedinCustomerId() {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getId();
        }
        return false;
    }
    /**
     * [getCustomerData description]
     * @return [type] [description]
     */
    public function getCustomerData() {
        if ($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomerData();
        }
        return false;
    }

    /**
     * checkAllowed function used to check for a valid access token.
     * @throws \Exception Authorization required. message as output.
     */
    private function _checkAllowed() {
        if($this->authorize->isAllowed('Fecon_ExternalCart::cart') === false) {
            throw new \Exception(
                __('Authorization required.')
            );
        }
    }
}