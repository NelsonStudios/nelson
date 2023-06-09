<?php


namespace Fecon\Shipping\Api\Data;

interface PreorderInterface
{

    const IS_AVAILABLE = 'is_available';
    const UPDATED_AT = 'updated_at';
    const CUSTOMER_ID = 'customer_id';
    const SHIPPING_METHOD = 'shipping_method';
    const QUOTE_ID = 'quote_id';
    const SHIPPING_PRICE = 'shipping_price';
    const CREATED_AT = 'created_at';
    const PREORDER_ID = 'preorder_id';
    const ADDRESS_ID = 'address_id';
    const STATUS = 'status';
    const STATUS_NEW = 1;
    const STATUS_PENDING = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELED = 4;
    const CART_DATA = 'cart_data';
    const COMMENTS = 'comments';


    /**
     * Get preorder_id
     * @return string|null
     */
    public function getPreorderId();

    /**
     * Set preorder_id
     * @param string $preorderId
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setPreorderId($preorderId);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get is_available
     * @return string|null
     */
    public function getIsAvailable();

    /**
     * Set is_available
     * @param string $isAvailable
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setIsAvailable($isAvailable);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get quote_id
     * @return string|null
     */
    public function getQuoteId();

    /**
     * Set quote_id
     * @param string $quoteId
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setQuoteId($quoteId);

    /**
     * Get address_id
     * @return string|null
     */
    public function getAddressId();

    /**
     * Set address_id
     * @param string $addressId
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setAddressId($addressId);

    /**
     * Get shipping_method
     * @return string|null
     */
    public function getShippingMethod();

    /**
     * Set shipping_method
     * @param string $shippingMethod
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setShippingMethod($shippingMethod);

    /**
     * Get shipping_price
     * @return string|null
     */
    public function getShippingPrice();

    /**
     * Set shipping_price
     * @param string $shippingPrice
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setShippingPrice($shippingPrice);

    /**
     * Set status
     * @param string $status
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setStatus($status);

    /**
     * Get status
     * @return int
     */
    public function getStatus();

    /**
     * Set cart_data
     * @param string $cartData
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setCartData($cartData);

    /**
     * Get cart_data
     * @return string|null
     */
    public function getCartData();

    /**
     * Get comments
     * @return string|null
     */
    public function getComments();

    /**
     * Set comments
     * @param string $comments
     * @return \Fecon\Shipping\Api\Data\PreorderInterface
     */
    public function setComments($comments);
}
