<?php


namespace Serfe\Shipping\Model;

use Serfe\Shipping\Api\Data\PreorderInterface;

class Preorder extends \Magento\Framework\Model\AbstractModel implements PreorderInterface
{

    protected $_eventPrefix = 'serfe_shipping_preorder';

    const AVAILABLE = 1;
    const NOT_AVAILABLE = 0;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Serfe\Shipping\Model\ResourceModel\Preorder');
    }

    /**
     * Get preorder_id
     * @return string
     */
    public function getPreorderId()
    {
        return $this->getData(self::PREORDER_ID);
    }

    /**
     * Set preorder_id
     * @param string $preorderId
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     */
    public function setPreorderId($preorderId)
    {
        return $this->setData(self::PREORDER_ID, $preorderId);
    }

    /**
     * Get created_at
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get is_available
     * @return string
     */
    public function getIsAvailable()
    {
        return $this->getData(self::IS_AVAILABLE);
    }

    /**
     * Set is_available
     * @param string $isAvailable
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     */
    public function setIsAvailable($isAvailable)
    {
        return $this->setData(self::IS_AVAILABLE, $isAvailable);
    }

    /**
     * Get customer_id
     * @return string
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get quote_id
     * @return string
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * Set quote_id
     * @param string $quoteId
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Get address_id
     * @return string
     */
    public function getAddressId()
    {
        return $this->getData(self::ADDRESS_ID);
    }

    /**
     * Set address_id
     * @param string $addressId
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     */
    public function setAddressId($addressId)
    {
        return $this->setData(self::ADDRESS_ID, $addressId);
    }

    /**
     * Get shipping_method
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * Set shipping_method
     * @param string $shippingMethod
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     */
    public function setShippingMethod($shippingMethod)
    {
        return $this->setData(self::SHIPPING_METHOD, $shippingMethod);
    }

    /**
     * Get shipping_price
     * @return string
     */
    public function getShippingPrice()
    {
        return $this->getData(self::SHIPPING_PRICE);
    }

    /**
     * Set shipping_price
     * @param string $shippingPrice
     * @return \Serfe\Shipping\Api\Data\PreorderInterface
     */
    public function setShippingPrice($shippingPrice)
    {
        return $this->setData(self::SHIPPING_PRICE, $shippingPrice);
    }
}
