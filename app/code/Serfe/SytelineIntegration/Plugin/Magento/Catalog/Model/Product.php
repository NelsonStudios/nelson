<?php

namespace Serfe\SytelineIntegration\Plugin\Magento\Catalog\Model;

/**
 * Plugin to detect if a product is Available based on Syteline Web Service
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class Product
{
    /**
     * Syteline  Helper
     *
     * @var \Serfe\SytelineIntegration\Helper\SytelineHelper 
     */
    protected $sytelineHelper;

    /**
     * Product Repository
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface 
     */
    protected $productRepository;

    public function __construct(
        \Serfe\SytelineIntegration\Helper\SytelineHelper $sytelineHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->sytelineHelper = $sytelineHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * isAvailable Plugin
     *
     * @param \Magento\Catalog\Model\Product $subject
     * @param boolean $result
     * @return boolean
     */
    public function afterIsAvailable(
        \Magento\Catalog\Model\Product $subject,
        $result
    ) {
        $returnValue = $result;
        if ($this->existsInSyteline($subject)) {
            $returnValue = $this->sytelineHelper->isProductAvailable($subject);
        }

        return $returnValue;
    }

    /**
     * Check if $product exists in Syteline
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    protected function existsInSyteline($product)
    {
        try {
            $loadedProduct = $this->productRepository->getById($product->getId());
            $exists = (bool) $loadedProduct->getExistsInSyteline();
        } catch (Exception $ex) {
            $exists = false;
        }

        return $exists;
    }
}
