<?php
namespace Serfe\ExternalCart\Api;

/**
 * 
 */
interface CartInterface
{
    /**
     * Get the token of the created guest cart
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
     */
    public function getCartInfo($cartId);
    /**
     * Function to add products into guest cart.
     * 
     * @api
     * @return \Serfe\ExternalCart\Api\CartInterface $productAdded object with product related information.
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * Function to get the checkout url to access to the checkout with cart items loaded.
     * 
     * @api
     * @return string $url The checkout url
     */
    public function getCheckoutUrl();
}