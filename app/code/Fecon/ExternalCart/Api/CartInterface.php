<?php
/**
 * Contributor company: Fecon.
 * Contributor Author : <fecon.com>
 * Date: 2018/08/02
 */
namespace Fecon\ExternalCart\Api;

/**
 * Interface CartInterface
 * @api
 */
interface CartInterface
{
    /**
     * Create and get new token of the created guest cart
     *
     * @api
     * @param boolean $forceGuest force to create cart id as guest.
     * @return string $token of created guest cart.
     * @throws \SoapFault response
     */
    public function createCartToken($forceGuest = false);
    /**
     * Set the token of the recently created guest cart
     *
     * @api
     * @param  string $cartId The cartId to save.
     * @return string $token of created guest cart.
     */
    public function setCartToken($cartId);
    /**
     * Get the token of the recently created guest cart
     *
     * @api
     * @return string $token of created guest cart.
     */
    public function getCartToken();
    /**
     * Get the cart information.
     *
     * @api
     * @param  string $cartId The cartId to search in
     * @return \Fecon\ExternalCart\Api\CartInterface $cartInfo The cart information as an object.
     * @throws \SoapFault response
     */
    public function getCartInfo($cartId);


    /**
     * @param boolean $isGuest
     * @param string $cartId
     * @param \Fecon\ExternalCart\Api\Data\CartInterface $cart The Item
     * @return \Magento\Quote\Api\Data\CartItemInterface[] Item.
     */
    public function addProductIntoCart($isGuest, $cartId, $cart);

    /**
     * Add/update the specified cart item.
     *
     * @param \Fecon\ExternalCart\Api\Data\CartInterface $cart The item.
     * @return \Magento\Quote\Api\Data\CartItemInterface[] Item.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified item could not be saved to the cart.
     * @throws \Magento\Framework\Exception\InputException The specified item or cart is not valid.
     */
    public function guestAddProductIntoCart($cart);

    /**
     * Function to get the cart url to access to the guest cart (with previously generated token).
     *
     * @api
     * @return string $url The cart url
     */
    public function getCartUrl();
    /**
     * Function to add products into cart but also:
     *  - Generate token if not exists
     *  - If token exists only add to cart in the current sessioned cart.
     *
     * @api
     * @param  $cartId The cart id token that should look like: 7c6aa34c8ed9ccdb71f78f7b25d047b1 (optional)
     * @param  $body The body json data that should looks like:
     * {
     *     "cartItem": {
     *         "quoteId": "7c6aa34c8ed9ccdb71f78f7b25d047b1", (optional)
     *         "sku": "BH-080",
     *         "qty": "1"
     *     }
     * }
     * @return \Fecon\ExternalCart\Api\CartInterface $productAdded object with product related information.
     * @throws \SoapFault response
     */
    public function addToCart();
    /**
     * Function to add products into guest cart.
     *
     * @api
     * @param  $body The body json data that should looks like:
     *
     * Request structure:
     *
     * {
     *   "GetCart": {
     *     "ErpSendShoppingCartRequest": {
     *       "BillTo": {
     *         "SiteAddress": {
     *           "CustomerId": "",
     *           "Line1": "",
     *           "City": "",
     *           "State": "",
     *           "Zipcode": "",
     *           "Country": ""
     *         }
     *       },
     *       "ShipTo": {
     *         "SiteAddress": {
     *           "CustomerId": "",
     *           "Line1": "",
     *           "City": "",
     *           "State": "",
     *           "Zipcode": "",
     *           "Country": ""
     *         }
     *       },
     *       "Comments": "",
     *       "DocumotoERPTransactionType": "",
     *       "DocumotoERPTransactionStatus": "",
     *       "ShoppingCartLines": {
     *         "ShoppingCartLine": [
     *           {
     *           "PartNumber": "",
     *           "Quantity": "",
     *           "UOM": "",
     *           "Line": ""
     *           },
     *           {
     *           "PartNumber": "",
     *           "Quantity": "",
     *           "UOM": "",
     *           "Line": ""
     *           }
     *         ]
     *       }
     *     }
     *   }
     * }
     *
     * Response structure:
     * [
     *   {
     *     "ErpResponse": {
     *       "Success": "true | false",
     *       "Message": "Cart submitted successfully.| Error message (*Possible errors)",
     *       "ReferenceNumber": cartId number | quote id number,
     *       "ExternalOrderID": "false" (always false since we're currently not generating any order)
     *     }
     *   }
     * ]
     *
     * *Possible errors:
     *   - Requested product doesn't exist
     *   - Request does not match any route. (Check Authorization header)
     *   - %fieldName is a required field (Check if user was logged-in correctly through the Magento API)
     *
     * @return \Fecon\ExternalCart\Api\CartInterface $response object with response information (Documoto style).
     * @throws \Exception response
     */
    public function submitCart();
}
