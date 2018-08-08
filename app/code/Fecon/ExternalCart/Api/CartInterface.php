<?php
/**
 * Contributor company: Fecon.
 * Contributor Author : Bruno <bruno@serfe.com>
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
     * @return string $token of created guest cart.
     * @throws \SoapFault response
     */
    public function createCartToken();
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
     * Function to add products into guest cart.
     * 
     * @api
     * @param  $cartId The cart id token that should look like: 7c6aa34c8ed9ccdb71f78f7b25d047b1
     * @param  $body The body json data that should looks like:
     * {
     *     "cartItem": {
     *         "quoteId": "7c6aa34c8ed9ccdb71f78f7b25d047b1",
     *         "sku": "BH-080",
     *         "qty": "1"
     *     }
     * } 
     * @return \Fecon\ExternalCart\Api\CartInterface $productAdded object with product related information.
     * @throws \SoapFault response
     */
    public function addProductIntoCart();
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
}