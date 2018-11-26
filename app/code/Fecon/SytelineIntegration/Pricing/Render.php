<?php

namespace Fecon\SytelineIntegration\Pricing;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

/**
 * Description of Render
 */
class Render extends \Magento\Catalog\Pricing\Render
{

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Construct
     *
     * @param Template\Context $context
     * @param Registry $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $data = [
            'price_render_handle' => 'catalog_product_prices',
            'use_link_for_as_low_as' => true
        ];
        /** @var PricingRender $priceRender */
        $priceRender = $this->getLayout()->createBlock('Magento\Framework\Pricing\Render', '', ['data' => $data]);
        if ($priceRender instanceof PricingRender) {
//            return 'Instace of PricingRender';
//            $product = $this->getProduct();
            $sku = '005-99-501';
            $product = $this->productRepository->get($sku);
            if ($product instanceof SaleableInterface) {
                $arguments = $this->getData();
                $arguments['render_block'] = $this;
                return $priceRender->render($this->getPriceTypeCode(), $product, $arguments);
            }
        }
        return parent::_toHtml();
    }
}