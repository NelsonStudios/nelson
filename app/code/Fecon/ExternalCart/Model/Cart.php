<?php
/**
 * Contributor company: Fecon.
 * Contributor Author : <fecon.com>
 * Date: 2018/08/02
 */
namespace Fecon\ExternalCart\Model;

use Fecon\ExternalCart\Api\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ProductFactory;

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
     * $port
     *
     * @var string
     */
    protected $access_token;
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
     * @var Request
     */
    protected $apiRequest;
    /**
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    private \Magento\Quote\Api\CartItemRepositoryInterface $repository;
    /**
     * @var QuoteIdMaskFactory
     */
    private QuoteIdMaskFactory $quoteIdMaskFactory;
    /**
     * @var Request
     */
    private Request $restRequest;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Fecon\ExternalCart\Model\Customer $customerModel
     * @param \Fecon\ExternalCart\Helper\Data $externalCartHelper
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Session                    $customerSession,
        \Magento\Checkout\Model\Session                    $checkoutSession,
        \Magento\Quote\Model\QuoteFactory                  $quoteFactory,
        \Magento\Integration\Model\Oauth\TokenFactory      $tokenModelFactory,
        \Magento\Framework\App\Request\Http                $request,
        \Fecon\ExternalCart\Model\Customer                 $customerModel,
        \Fecon\ExternalCart\Helper\Data                    $externalCartHelper,
        \Magento\Framework\Webapi\Rest\Request             $apiRequest,
        \Magento\Quote\Api\CartItemRepositoryInterface     $repository,
        QuoteIdMaskFactory                                 $quoteIdMaskFactory,
        Request                                            $restRequest,
        ProductRepository                                  $productRepository,
        ProductFactory                                     $productFactory
    )
    {
        $this->cartHelper = $externalCartHelper;
        /**
         * First check if it's allowed to use the API.
         */
        //$this->cartHelper->checkAllowed();

        $this->coreSession = $coreSession;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->customerModel = $customerModel;
        $this->request = $request;
        $this->apiRequest = $apiRequest;
        $this->protocol = $this->cartHelper->protocol();
        $this->hostname = $this->cartHelper->hostname();
        $this->port = $this->cartHelper->port();
        $this->access_token = $this->cartHelper->access_token();

        if (!empty($this->protocol) && !empty($this->hostname)) {
            $this->origin = $this->protocol . $this->hostname;
        }
        if (!empty($this->port)) {
            $this->origin .= ':' . $this->port;
        }
        /* Add backend settings validation */
        if (empty($this->origin)) {
            throw new \Exception(
                __('Please check External Cart Settings in Admin section.')
            );
        }
        /**
         * Get customer id or false otherwise.
         */
        $this->customerToken = $this->customerSession->getData('loggedInUserToken');
        $this->setEndpoints($this->customerToken);
        $this->repository = $repository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->restRequest = $restRequest;
        $this->_productRepository = $productRepository;
        $this->_productFactory = $productFactory;
    }

    /**
     * Create and get new token of the created guest/customer cart
     *
     * @api
     * @return string $token of created guest cart.
     * @throws \SoapFault response
     * @api
     */
    public function createCartToken($forceGuest = false)
    {
        if ($forceGuest) {
            $this->setEndpoints(false);
            $this->customerSession->setData('loggedInUserToken', '');
        }
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->quoteCartManagementV1, (($this->opts) ? $this->opts : []));
        try {
            $token = '';
            if (!$forceGuest && $this->customerToken) {
                $token = $this->cartHelper->getActiveQuoteForCustomer($this->customerSession->getId());
                $this->checkoutSession->setQuoteId($token);
                $this->setCartToken($token);
            } else {
                $token = $client->{$this->quoteCartManagementV1Endpoint}(['']);
                $token = $token->result;
            }
            $this->setCartToken($token);
            return $token;
        } catch (\SoapFault $e) {
            return $e->getMessage();
        }
    }

    /**
     * Set the token of the recently created guest cart
     *
     * @param string $cartId The cartId to save.
     * @return string $cartId
     * @api
     */
    public function setCartToken($cartId)
    {
        $this->coreSession->start();
        $this->coreSession->setCartId($cartId);
        return $this->getCartToken();
    }

    /**
     * Get the token of the recently created guest cart
     *
     * @return string $token of created guest cart or empty array otherwise.
     * @api
     */
    public function getCartToken()
    {
        $this->coreSession->start();
        return $this->coreSession->getCartId();
    }

    /**
     * Get the cart information.
     *
     * @param string $cartId The cartId to search in
     * @return stdObject $cartInfo The cart information as an object.
     * @throws \SoapFault response
     * @api
     */
    public function getCartInfo($cartId)
    {
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->quoteCartRepositoryV1, (($this->opts) ? $this->opts : []));
        try {
            $cartInfo = $client->{$this->quoteCartRepositoryV1Get}(array('cartId' => $cartId));
            return $this->cartHelper->jsonResponse($cartInfo->result); //Return cartInfo result object with cart information.
        } catch (\SoapFault $e) {
            return $e->getMessage();
        }
    }

    /**
     * create new product if not exits.
     * @param $cartItem
     * @throws \Exception
     */
    public function handleProductBeforeSaveCart($cartItem): void {
        try {
            $product =  $this->_productRepository->get($cartItem->getSku());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e){
            $_product = $this->_productFactory->create();
            $_product->setName($cartItem->getSku());
            $_product->setTypeId('simple');
            $_product->setAttributeSetId(4);
            $_product->setSku($cartItem->getSku());
            $_product->setWeight(1.0000);
            $_product->setStatus(1);
            $_product->setWebsiteIds(array(1));
            $_product->setVisibility(3);
            $_product->setPrice(10);
            $_product->setExistsInSyteline(1);
            $_product->setStockData(array(
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'min_sale_qty' => 1,
                    'is_in_stock' => 1,
                    'qty' => 999999
                )
            );
            $_product->save();
        }
    }

    /**
     * @param $isGuest
     * @param $cartId
     * @param $cart
     * @return CartItemInterface[]
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function addProductIntoCart($isGuest, $cartId, $cart)
    {
        if ($isGuest) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $quoteId = $quoteIdMask->getQuoteId();
        } else {
            $quoteId = $cartId;
        }
        foreach ($cart->getCartItems() as $cartItem) {
            $cartItem->setQuoteId($quoteId);
            $this->handleProductBeforeSaveCart($cartItem);
            $this->repository->save($cartItem);
        }
        $cartItemList = $this->repository->getList($quoteId);
        /** @var $item CartItemInterface */
        foreach ($cartItemList as $item) {
            $item->setQuoteId($quoteId);
        }
        $result = $cartItemList;
        return $result;
    }

    /**
     * @inheirtDoc
     */
    public function guestAddProductIntoCart($cart)
    {
        $cartId = $this->restRequest->getParam('cartId');
        $result = [];
        if ($cartId) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            foreach ($cart->getCartItems() as $cartItem) {
                $cartItem->setQuoteId($quoteIdMask->getQuoteId());
                $this->repository->save($cartItem);
            }
            $cartItemList = $this->repository->getList($quoteIdMask->getQuoteId());
            /** @var $item CartItemInterface */
            foreach ($cartItemList as $item) {
                $item->setQuoteId($quoteIdMask->getMaskedId());
            }
            $result = $cartItemList;
        }
        return $result;
    }

    /**
     * Function to get the cart url to access to the guest cart (with previously generated token).
     *
     * @return string $url The cart url
     * @api
     */
    public function getCartUrl()
    {
        $cartId = $this->getCartToken();
        $customerToken = $this->customerSession->getData('loggedInUserToken');
        if (!empty($customerToken)) { //It's a customerToken
            return $this->origin . '/externalcart/cart/?customerToken=' . $this->customerToken;
        } else if (!empty($cartId)) {
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
     * @param cartId The cart id where you want to store the items (optional, if is not sent, a new will be created and used in next requests)
     * @param body The body (json object) post data with items you want to store. (quoteId is optional)
     * @return \Fecon\ExternalCart\Api\CartInterface $productAdded object with product related information.
     * @throws \SoapFault response
     * @api
     */
    public function addToCart()
    {
        /* Get post data */
        $postData = $this->request->getPost();
        $cartIdFromSession = $this->getCartToken();
        /* If postData['cartId'] is not there but in session */
        if (empty($postData['cartId']) && !empty($cartIdFromSession)) {
            $postData['cartId'] = $cartIdFromSession;
        } else if (empty($postData['cartId']) && empty($cartIdFromSession)) { // Create new cartId
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
     * @api
     */
    private function _addProduct($postData, $updateCartId = false)
    {
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->quoteCartItemRepositoryV1, (($this->opts) ? $this->opts : []));

        if (!is_array($postData['body'])) {
            $cartItemData = $this->cartHelper->jsonDecode($postData['body'], 1);
        } else {
            $cartItemData = $postData['body'];
        }
        if ($updateCartId) {
            /* Update cartId in body post data */
            $cartItemData['cartItem']['quoteId'] = (($postData['quoteId']) ? $postData['quoteId'] : $postData['cartId']);
        }
        $productData = [
            'cartId' => (($postData['quoteId']) ? $postData['quoteId'] : $postData['cartId']),
            'cartItem' => $cartItemData['cartItem']
        ];
        try {
            $productAdded = $client->{$this->quoteCartItemRepositoryV1Save}($productData);
            if ($updateCartId) {
                $productAdded->result->cartId = (($postData['quoteId']) ? $postData['quoteId'] : $postData['cartId']);
            }
            return $this->cartHelper->jsonResponse($productAdded->result); //Return cartInfo result object with cart information.
        } catch (\SoapFault $e) {
            $this->cartHelper->sendAdminErrorNotification($e->getMessage());
            return $e->getMessage() . nl2br("\n Are you sure this is the correct cartId?", false);
        }
    }

    /**
     * setEndpoints function to set proper endpoints paths (customer or guest) and options
     * for SOAP client.
     *
     * @param void
     */
    private function setEndpoints($customer)
    {
        /* Regarding user type get proper settings and options to make SOAP requests */
        $settings = $this->cartHelper->checkUserTypeAndSetEndpoints($customer);
        foreach ($settings['endpointsPaths'] as $k => $v) {
            $this->{$k} = $v;
            /* Above line (explanation) will be the same to do the following down below:
             *   $this->quoteCartManagementV1 = $settings['endpointsPaths']['quoteCartManagementV1'];
             */
        }
        if (!empty($settings['opts'])) {
            $this->opts = $settings['opts'];
        }
    }

    /**
     * submitCart function
     *  Steps to get this working
     *   - Check if customer exists, if not return error response.
     *   - Get customer token in order to perform further requests.
     *   - Set Magento API REST endpoints for logged-in customers.
     *   - Perform customer autologin in order to add the products to the customer cart.
     *   - Get quote id for the logged-in customer.
     *   - Then add products
     *     - If product doesn't exist, send admin notification (email)
     *     - Return error response.
     *
     * @param  $body The body json data that should looks like:
     *         - See iterface for more information.
     * @return mixed $response json response or exteption.
     */
    public function submitCart()
    {
        /* Post data formatted as Documoto requested. (See Api/Cart interface for more info) */
        $postData = $this->apiRequest->getBodyParams();

        if (!empty($postData)) {
            /* Request array structure to send to Magento 2 Rest API */
            $productDataMap = ['quoteId' => '', 'body' => ['cartItem' => []]];
            /* Array with result of products added or error */
            $productsAdded = [];
            /* Transform post data from body into an array to easily handle the data. */
//            $cartData = $this->cartHelper->jsonDecode($postData);
            $cartData = $postData;
            /* Validation for shopping cart, check if there're products to add into the Magento cart, if not throw an exception */
            if (empty($cartData['GetCart']['ErpSendShoppingCartRequest']['ShoppingCartLines']['ShoppingCartLine'])) {
                $errMsg = 'Error, empty ShoppingCartLine no products to add.';
                $this->cartHelper->sendAdminErrorNotification($errMsg);
                throw new \Exception(
                    __($errMsg)
                );
            }
            $shippingCartData = [
                'ShoppingCartLine' => $cartData['GetCart']['ErpSendShoppingCartRequest']['ShoppingCartLines']['ShoppingCartLine']
            ];
            $documotoUsername = $cartData['GetCart']['ErpSendShoppingCartRequest']['Username'];
            /* Get customer data */
            $customerDataRaw = $this->customerModel->getCustomerByDocumotoUsername($documotoUsername);

            /* This is only to avoid to access subzero in further var usage. */
            $customerData = $customerDataRaw[0];
            unset($customerDataRaw);
            /* Get customer token otherwise we can't add products into cart as a valid logged-in user
             * Verified: entity_id is the customer id.
             */
            if (!empty($customerData['entity_id'])) {
                /* Instance tokenModelFactory */
                $customerToken = $this->tokenModelFactory->create();
                /* Create customer token based on customer id */
                $this->customerToken = $customerToken->createCustomerToken($customerData['entity_id'])->getToken();
                /* Set customer token in session (max 1 hour by default) */
                $this->customerSession->setData('loggedInUserToken', $this->customerToken);
                /* If we not set endpoints for logged-in customer, this will fail on first execution time */
                $this->setEndpoints($this->customerToken);
            }

            /* Make customer autologin
             * Before run this, you should ensure that user was logged-in through Magento 2 API rest, otherwise this will fail
             */
            if (!empty($customerData['entity_id'])) {
                $requestData = ['customerId' => $customerData['entity_id']];
                /* Perform user login */
                $this->cartHelper->makeUserLogin($customerData['email']);
            }
            unset($customerData);
            /* Get quote id before add products into the cart
             * byPass Authorization access for internal use only
             */
            $opts['stream_context'] = stream_context_create([
                'http' => [
                    'header' => sprintf('Authorization: Bearer %s', $this->customerToken)
                ]
            ]);
            $client = new \SoapClient($this->origin . '/soap/?wsdl&services=quoteCartManagementV1', $opts);
            try {
                /* Create empty cart */
                //$client->quoteCartManagementV1CreateEmptyCartForCustomer(((!empty($requestData))? $requestData : '' ));
                /* Get quote */
                $cartInfo = $client->quoteCartManagementV1GetCartForCustomer(((!empty($requestData)) ? $requestData : '')); /* If $requestData is empty an exception is thrown */
                if (!empty($cartInfo->result->id)) {
                    $quoteId = $cartInfo->result->id;
                    unset($cartInfo);
                    /* Load quote */
                    $q = $this->quoteFactory->create()->load($quoteId);
                    /* Load in checkout session */
                    $this->checkoutSession->setQuoteId($quoteId);
                } else {
                    throw new \Exception(
                        __('Error, there\'s no cart id.')
                    );
                }
            } catch (\SoapFault $e) {
                return $e->getMessage();
            }
            /* Get current quote
             * !Without this param we'll not be able to add products into the logged-in customer cart.
             */
            $productDataMap['quoteId'] = $quoteId;
            /*
             * Add products
             */
            foreach ($shippingCartData['ShoppingCartLine'] as $key => $productData) {
                $productDataMap['body']['cartItem'] = [
                    'sku' => (!empty($productData['Sku']) ? $productData['Sku'] : $productData['PartNumber']),
                    'qty' => $productData['Quantity']
                ];
                /* User should be logged-in in order the validation for product works, otherwise another error will be triggered. */
                array_push($productsAdded, $this->_addProduct($productDataMap, true));
            }
            if (!empty($productsAdded)) {
                foreach ($productsAdded as $key => $product) {
                    $p = json_decode($product);
                    if (empty($p) || (!empty($p) && !is_object($p))) {
                        $response = ['body' => [
                            'ErpResponse' => [
                                'Success' => 'false',
                                'Message' => $product,
                                'ReferenceNumber' => 'N/A',
                                'ExternalOrderID' => 'false'
                            ]
                        ]
                        ];
                        /* Breakpoint here and return error response and send via email */
                        $this->cartHelper->sendAdminErrorNotification($product);
                        return $response;
                    }
                }
                $response = ['body' => [
                    'ErpResponse' => [
                        'Success' => 'true',
                        'Message' => 'Cart submitted successfully.',
                        'ReferenceNumber' => $productDataMap['quoteId'],
                        'ExternalOrderID' => 'false',
                        'RedirectionURL' => $this->getCartUrl()
                    ]
                ]
                ];
                /* Return success response */
                return $response;
            }
        }
    }
}
