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
     * $customerToken
     * @var mixed integer/boolean
     */
    protected $customerToken;
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
     * $opts 
     * Options array to be sent in SOAP request.
     * @var array
     */
    protected $opts;

    /**
     * Constructor
     * 
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession       
     * @param \Magento\Customer\Model\SessionFactory             $customerSession   
     * @param \Magento\Framework\App\Request\Http                $request           
     * @param \Fecon\ExternalCart\Helper\Data                    $externalCartHelper
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Fecon\ExternalCart\Model\Customer $customerModel,
        \Fecon\ExternalCart\Helper\Data $externalCartHelper
    ) {
        $this->cartHelper = $externalCartHelper;
        /**
         * First check if it's allowed to use the API.
         */
        $this->cartHelper->checkAllowed();

        $this->coreSession = $coreSession;
        $this->customerSession = $customerSession;
        $this->customerModel = $customerModel;
        $this->request = $request;

        $this->protocol = $this->cartHelper->protocol();
        $this->hostname = $this->cartHelper->hostname();
        $this->port = $this->cartHelper->port();

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
        $this->customerToken = $this->customerSession->getData('loggedInUserToken');
        $this->setEndpoints($this->customerToken);
    }
    /**
     * Create and get new token of the created guest/customer cart
     *
     * @api
     * @return string $token of created guest cart.
     * @throws \SoapFault response
     */
    public function createCartToken($forceGuest = false) {
        if($forceGuest) {
            $this->setEndpoints(false);
            $this->customerSession->setData('loggedInUserToken', '');
        }
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->quoteCartManagementV1, (($this->opts)? $this->opts : [] ));
        try {
            $token = $client->{$this->quoteCartManagementV1Endpoint}(((!$forceGuest && $this->customerToken)? ['customerId' => $this->customerSession->getId()] : ''));
            if(!empty($token->result->id)) {
                // Magneto 2 return an object with cart data instead a token here.
                $this->setCartToken($token->result->id);
                return $token->result->id;
            } else {
                /* Store cartId token in session temporary */
                $this->setCartToken($token->result);
                return $token->result; //token id of recently created cart.
            }
        } catch(\SoapFault $e) {
            return $e->getMessage();
        }
    }
    /**
     * Set the token of the recently created guest cart
     *
     * @api
     * @param  string $cartId The cartId to save.
     * @return string $cartId
     */
    public function setCartToken($cartId) {
        $this->coreSession->start();
        $this->coreSession->setCartId($cartId);
        return $this->getCartToken();
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
     * @param  string $cartId The cartId to search in
     * @return stdObject $cartInfo The cart information as an object.
     * @throws \SoapFault response
     */
    public function getCartInfo($cartId) {
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->quoteCartRepositoryV1, (($this->opts)? $this->opts : '' ));
        try {
            $cartInfo = $client->{$this->quoteCartRepositoryV1Get}(array('cartId' => $cartId));
            return $this->cartHelper->jsonResponse($cartInfo->result); //Return cartInfo result object with cart information.
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
        $customerToken = $this->customerSession->getData('loggedInUserToken');
        if(!empty($cartId) && $customerToken) { //It's a customerToken
            return $this->origin . '/externalcart/cart/?customerToken=' . $customerToken;
        } else if(!empty($cartId)) {
            //Make sure user is logged out.
            $this->customerSession->logout();
            return $this->origin . '/externalcart/cart/?cartId=' . $cartId;
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
     * Updated to set quoteId of the customer if customer id is sent, user must be logged-in first.
     * 
     * @api
     * @param $postData array with product data.
     * @return integer $productId on success otherwise throws SoapFault
     * @throws \SoapFault response
     */
    private function _addProduct($postData, $updateCartId = false) {
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->quoteCartItemRepositoryV1, (($this->opts)? $this->opts : [] ));

        if(!is_array($postData['body'])) {
            $cartItemData = $this->cartHelper->jsonDecode($postData['body'], 1);
        } else {
            $cartItemData = $postData['body'];
        }
        if($updateCartId) {
            /* Update cartId in body post data */
            $cartItemData['cartItem']['quoteId'] = (($postData['quoteId'])? $postData['quoteId'] : $postData['cartId']);
        }
        $productData = [
            'cartId' => (($postData['quoteId'])? $postData['quoteId'] : $postData['cartId']), 
            'cartItem' => $cartItemData['cartItem']
        ];
        try {
            //TOOD: if product doesn't exist on Magento return error.
            $productAdded = $client->{$this->quoteCartItemRepositoryV1Save}($productData);
            if($updateCartId) {
                $productAdded->result->cartId = (($postData['quoteId'])? $postData['quoteId'] : $postData['cartId']);
            }
            return $this->cartHelper->jsonResponse($productAdded->result); //Return cartInfo result object with cart information.
        } catch(\SoapFault $e) {
            return $e->getMessage() . nl2br("\n Are you sure this is the correct cartId?", false);
        }
    }
    /**
     * setEndpoints function to set proper endpoints paths (customer or guest) and options
     * for SOAP client.
     * 
     * @param void
     */
    private function setEndpoints($customer) {
        /* Regarding user type get proper settings and options to make SOAP requests */
        $settings = $this->cartHelper->checkUserTypeAndSetEndpoints($customer);
        foreach($settings['endpointsPaths'] as $k => $v) {
            $this->{$k} = $v;
            /* Above line (explanation) will be the same to do the following down below:
             *   $this->quoteCartManagementV1 = $settings['endpointsPaths']['quoteCartManagementV1'];
             */
        }
        if(!empty($settings['opts'])) {
            $this->opts = $settings['opts'];
        }
    }
    /**
     * submitCart function | check: https://tracker.serfe.com/view.php?id=52950#c442215
     *  Steps to get this working
     *   - Check if user exists, if not return error response
     *     - Check if user has billing and/or shipping address, if it has: update, if not: create a new one.
     *   - Perform user autologin in order to add the products to the customer.
     *   - Get quote id for the logged-in customer.
     *   - Then add products
     *     - If product doesn't exist, send admin notification (email)
     *     - Return error response.
     */
    public function submitCart() {
        $postData = $this->request->getPost();
        $productDataMap = ['quoteId' => '27', 'body' => ['cartItem' => []]];
        $productsAdded = [];
        // Transform post data from body into an array to easily handle the data.
        $cartData = $this->cartHelper->jsonDecode($postData['body']);
        $customerAddressData = [
            'BillTo' => $cartData['GetCart']['ErpSendShoppingCartRequest']['BillTo'],
            'ShipTo' => $cartData['GetCart']['ErpSendShoppingCartRequest']['ShipTo']
        ];
        $shippingCartData = [
            'ShoppingCartLine' => $cartData['GetCart']['ErpSendShoppingCartRequest']['ShoppingCartLines']['ShoppingCartLine']
        ];
        if(!empty($postData)) {
            // Get customer data
            $customerData = $this->customerModel->getCustomerByDocumotoId($customerAddressData['BillTo']['SiteAddress']['CustomerId']);
            // Set/update billing address
            $billingAddress = $this->customerModel->setCustomerAddress($customerData[0], $customerAddressData, 'BillTo');
            // Set/update shipping address
            $shippingAddress = $this->customerModel->setCustomerAddress($customerData[0], $customerAddressData, 'ShipTo');
            // Make customer autologin
            $this->cartHelper->makeUserLogin($customerData[0]['email']);
            // Get quote id before add products into the cart
            $token = $this->getCartToken();
            if(empty($token)) {
                $token = $this->createCartToken();
            }

            // $localAccessToken = 'fd35xyhc2cun28w39prottpekbvrv12e';
            // $stagingAccessToken = 'j2u1n6bqmtj6w0kfqf3m25m33qv1e8km';
            // $quoteId = $this->cartHelper->makeCurlRequest($this->origin, '/rest/V1/customers/'. $customerData[0]['entity_id'] .'/carts', $stagingAccessToken, 'GET');
            /* Without this param we'll not be able to get logged in customer cart. */
            // $productDataMap['quoteId'] = $quoteId;
            // Add products
            foreach ($shippingCartData['ShoppingCartLine'] as $key => $productData) {
                $productDataMap['body']['cartItem'] = [
                    'sku' => $productData['PartNumber'],
                    'qty' => $productData['Quantity']
                ];
                /* TODO: check why _addProduct is not returning error when product doesn't exist */
                array_push($productsAdded, $this->_addProduct($productDataMap, true));
            }
            echo '<pre>';
            print_r($productsAdded);
            echo '</pre>';
            exit;
            if(!empty($productsAdded)) {
                foreach ($productsAdded as $key => $product) {
                    $p = json_decode($product);
                    if(!empty($p) && !is_object($p)) {
                        $response = ['body' => [
                                'ErpResponse' => [
                                    'Success' => 'false',
                                    'Message' => $p,
                                    'ReferenceNumber' => 'N/A',    
                                    'ExternalOrderID' => 'false'
                                ]
                            ]
                        ];
                        // Breakpoint here and return error
                        return $response;
                    }
                }
                $response = ['body' => [
                        'ErpResponse' => [
                            'Success' => 'true',
                            'Message' => 'Cart submitted successfully.',
                            'ReferenceNumber' => $productDataMap['quoteId'],    
                            'ExternalOrderID' => 'false'
                        ]
                    ]
                ];
                /* Return success response */
                return $response;
            }
        }
    }
}