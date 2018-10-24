<?php

namespace Fecon\SytelineIntegration\Helper;

/**
 * Cache Helper
 */
class CacheHelper
{

    const PRICE_CACHE_PREFIX = 'PRICE-';
    const SPECIAL_PRICE_CACHE_SUFIX = '-SPECIAL';

    protected $cacheType;

    public function __construct(
        \Fecon\SytelineIntegration\Model\Cache\Type $cacheType
    ) {
        $this->cacheType = $cacheType;
    }

    public function savePrice($price, $productId, $sytelineCustomerId, $specialPrice = false)
    {
        $priceStr = (string) $price;
        $identifier = $this->getPriceIdentifier($productId, $sytelineCustomerId, $specialPrice);
        $this->cacheType->save($priceStr, $identifier);
    }

    public function getPrice($productId, $sytelineCustomerId, $specialPrice = false)
    {
        $identifier = $this->getPriceIdentifier($productId, $sytelineCustomerId, $specialPrice);
        $price = $this->cacheType->load($identifier);

        return $price;
    }

    protected function getPriceIdentifier($productId, $sytelineCustomerId, $specialPrice = false)
    {
        $identifier = $this::PRICE_CACHE_PREFIX . $productId . $sytelineCustomerId;
        if ($specialPrice) {
            $identifier .= $this::SPECIAL_PRICE_CACHE_SUFIX;
        }

        return $identifier;
    }
}