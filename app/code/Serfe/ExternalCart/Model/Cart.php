<?php
namespace Serfe\ExternalCart\Model;

use Serfe\ExternalCart\Api\CartInterface;
 
/**
 * Cart model
 */
class Cart implements CartInterface
{
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
     * $storeManagerInterface
     * 
     * @var \Magento\Store\Model\StoreManagerInterface 
     */
    protected $storeManagerInterface;
    /**
     * $request
     * 
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
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
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\Request\Http $request,
        \Serfe\ExternalCart\Helper\Data $externalCartHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->coreSession = $coreSession;
        $this->storeManager = $storeManagerInterface;
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
    }
    /**
     * Create and get new token of the created guest cart
     *
     * @api
     * @return string $token of created guest cart.
     * @throws \SoapFault response
     */
    public function createCartToken() {
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=quoteGuestCartManagementV1');
        try {
            $token = $client->quoteGuestCartManagementV1CreateEmptyCart();
            /* Store cartId token in session temporary */
            $this->setCartToken($token->result);
            return $token->result; //token id of recently created cart.
        } catch(\SoapFault $e) {
            echo $e->getMessage();
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
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=quoteGuestCartRepositoryV1');
        try {
            $cartInfo = $client->quoteGuestCartRepositoryV1Get(array('cartId' => $cartId));
            return $this->jsonResponse($cartInfo->result); //Return cartInfo result object with cart information.
        } catch(\SoapFault $e) {
            echo $e->getMessage();
        }
    }
    /**
     * Function to add products into guest cart.
     * 
     * @api
     * @param  string $cartId created guest cart id.
     * @param  array  $cartItem array with product data
     * @return integer $productId on success otherwise throws SoapFault
     * @throws \SoapFault response
     */
    public function addProductIntoCart() {
        $postData = $this->request->getPost();
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=quoteGuestCartItemRepositoryV1');
        /* Test data with existent product. */
        // $productData = [
        //     'cartId' => $postData['cartId'], 
        //     'cartItem' => [
        //         'quoteId' => $postData['cartId'],
        //         'sku' => 'BH-300',
        //         'qty' => 1
        //     ]
        // ];
        $cartItemData = $this->jsonHelper->jsonDecode($postData['body'], 1);
        $productData = [
            'cartId' => $postData['cartId'], 
            'cartItem' => $cartItemData['cartItem']
        ];
        try {
            $productAdded = $client->quoteGuestCartItemRepositoryV1Save($productData);
            return $this->jsonResponse($productAdded->result); //Return cartInfo result object with cart information.
        } catch(\SoapFault $e) {
            echo $e->getMessage() . "\nAre you sure this is the correct cartId?";
        }
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
            return $this->storeManager->getStore()->getBaseUrl() . 'externalcart/cart/?cartId=' . $this->getCartToken();
        }
        return false;
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
}