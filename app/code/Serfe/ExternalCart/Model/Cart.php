<?php
namespace Serfe\ExternalCart\Model;

use Serfe\ExternalCart\Api\CartInterface;
 
/**
 * 
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
     * $request
     * 
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * [$protocol description]
     * @var string
     */
    protected $protocol = 'https://';
    /**
     * [$hostname description]
     * @var string
     */
    protected $hostname = 'fecom.devphase.io';
    /**
     * [$port description]
     * @var string
     */
    protected $port = '';
    /**
     * [$origin description]
     * @var [type]
     */
    protected $origin;

    /**
     * Constructor
     * 
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->origin = $this->protocol . $this->hostname . $this->port;
        $this->jsonHelper = $jsonHelper;
        $this->request = $request;
    }
    /**
     * Get the token of the created guest cart
     *
     * @api
     * @return string $token of created guest cart.
     */
    public function getCartToken() {
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=quoteGuestCartManagementV1');
        $token = $client->quoteGuestCartManagementV1CreateEmptyCart();
        return $token->result; //token id of recently created cart.
    }
    /**
     * Get the cart information.
     * 
     * @api
     * @return stdObject $cartInfo The cart information as an object.
     */
    public function getCartInfo($cartId) {
        $client = new \SoapClient($this->origin . '/soap/?wsdl&services=quoteGuestCartRepositoryV1');
        $cartInfo = $client->quoteGuestCartRepositoryV1Get(array('cartId' => $cartId));
        echo $this->jsonResponse($cartInfo->result); //Return cartInfo result object with cart information.
        exit;
    }
    /**
     * Function to add products into guest cart.
     * 
     * @api
     * @param  string $cartId created guest cart id.
     * @param  array  $cartItem array with product data
     * @return integer $productId on success otherwise throws LocalizedException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProductIntoCart() {
        $postData = $this->request->getPost();
        // echo '<pre>';
        // print_r(json_decode($postData['body']['cartItem'], 1));
        // echo '</pre>';
        // exit;
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
        $productAdded = $client->quoteGuestCartItemRepositoryV1Save($productData);
            
        echo $this->jsonResponse($productAdded->result); //Return cartInfo result object with cart information.
        exit;

    }
    /**
     * Function to get the cart url to access to the guest cart (with previously generated token).
     * 
     * @api
     * @return string $url The cart url
     */
    public function getCartUrl() {

    }
    /**
     * Function to get the checkout url to access to the checkout with cart items loaded.
     * 
     * @api
     * @return string $url The checkout url
     */
    public function getCheckoutUrl() {


    }
    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->jsonHelper->jsonEncode($response);
    }
}