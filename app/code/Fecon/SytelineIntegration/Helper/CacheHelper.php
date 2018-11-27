<?php

namespace Fecon\SytelineIntegration\Helper;

/**
 * Cache Helper
 */
class CacheHelper
{

    const PRICE_CACHE_PREFIX = 'PRICE-';
    const SPECIAL_PRICE_CACHE_SUFIX = '-SPECIAL';

    /**
     * @var \Fecon\SytelineIntegration\Model\Cache\Type 
     */
    protected $cacheType;

    /**
     * Constructor
     *
     * @param \Fecon\SytelineIntegration\Model\Cache\Type $cacheType
     */
    public function __construct(
        \Fecon\SytelineIntegration\Model\Cache\Type $cacheType
    ) {
        $this->cacheType = $cacheType;
    }

    /**
     * Save price into cache
     *
     * @param float $price
     * @param int $productId
     * @param string $sytelineCustomerId
     * @param boolean $specialPrice
     * @return void
     */
    public function savePrice($price, $productId, $sytelineCustomerId, $specialPrice = false)
    {
        $priceStr = (string) $price;
        $identifier = $this->getPriceIdentifier($productId, $sytelineCustomerId, $specialPrice);
        $this->cacheType->save($priceStr, $identifier);
    }

    /**
     * Get price from cache
     *
     * @param int $productId
     * @param string $sytelineCustomerId
     * @param boolean $specialPrice
     * @return float|boolean        Returns false y price has been not found in cache
     */
    public function getPrice($productId, $sytelineCustomerId, $specialPrice = false)
    {
        $identifier = $this->getPriceIdentifier($productId, $sytelineCustomerId, $specialPrice);
        $cachePrice = $this->cacheType->load($identifier);
        $price = ($cachePrice !== false)? (float) $cachePrice : $cachePrice;

        return $price;
    }

    /**
     * Get price identifier to save/retrieve it to/from cache
     *
     * @param int $productId
     * @param string $sytelineCustomerId
     * @param boolean $specialPrice
     * @return string
     */
    protected function getPriceIdentifier($productId, $sytelineCustomerId, $specialPrice = false)
    {
        $identifier = $this::PRICE_CACHE_PREFIX . $productId . $sytelineCustomerId;
        if ($specialPrice) {
            $identifier .= $this::SPECIAL_PRICE_CACHE_SUFIX;
        }

        return $identifier;
    }
}