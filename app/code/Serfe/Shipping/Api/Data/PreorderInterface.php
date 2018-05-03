<?php


namespace Serfe\Shipping\Api\Data;

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


    /**
     * Get preorder_id
     * @return string|null
     */
    public function getPreorderId();

    /**
     * Set preorder_id
     * @param string $preorderId
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
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
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
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
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
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
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
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
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
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
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
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
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
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
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
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
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     */
    public function setShippingPrice($shippingPrice);
}
