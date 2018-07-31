<?php
namespace Serfe\ExternalCart\Api;

/**
 * Cart Interface
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
     * @return \Serfe\ExternalCart\Api\CartInterface $cartInfo The cart information as an object.
     * @throws \SoapFault response
     */
    public function getCartInfo($cartId);
    /**
     * Function to add products into guest cart.
     * 
     * @api
     * @return \Serfe\ExternalCart\Api\CartInterface $productAdded object with product related information.
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
}