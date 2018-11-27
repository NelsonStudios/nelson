<?php

namespace Fecon\SytelineIntegration\Pricing\Render;

use Magento\Customer\Model\Group;

/**
 * Price amount renderer
 */
class Amount extends \Magento\Framework\Pricing\Render\Amount
{

    /**
     * @var \Magento\Customer\Model\Session 
     */
    protected $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Pricing\Amount\AmountInterface $amount
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Pricing\Render\RendererPool $rendererPool
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Pricing\SaleableInterface $saleableItem
     * @param \Magento\Framework\Pricing\Price\PriceInterface $price
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Pricing\Amount\AmountInterface $amount,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Pricing\Render\RendererPool $rendererPool,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\SaleableInterface $saleableItem = null,
        \Magento\Framework\Pricing\Price\PriceInterface $price = null,
        array $data = array()
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $amount, $priceCurrency, $rendererPool, $saleableItem, $price, $data);
    }

    /**
     * Checks if price should be rendered async or not
     *
     * @return boolean
     */
    public function shouldReloadPrice()
    {
        $action = $this->getRequest()->getFullActionName();
        $shouldReload = false;
        $customerGroup = $this->customerSession->getCustomerGroupId();
        if ($action == 'catalog_product_view' &&
            $customerGroup != Group::NOT_LOGGED_IN_ID
        ) {
            $shouldReload = true;
        }

        return $shouldReload;
    }

    /**
     * Get endpoint url
     *
     * @return string
     */
    public function getRenderPriceEndpointUrl()
    {
        $routePath = 'syteline/price/render';

        return $this->_urlBuilder->getUrl($routePath);
    }
}