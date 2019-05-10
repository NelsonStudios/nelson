<?php

namespace Fecon\SytelineIntegration\Block\Cart;

/**
 * Description of Grid
 */
class Grid extends \Magento\Checkout\Block\Cart\Grid
{

    /**
     * @var \Fecon\SytelineIntegration\Helper\SytelineHelper
     */
    protected $sytelineHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param \Fecon\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        \Fecon\SytelineIntegration\Helper\SytelineHelper $sytelineHelper,
        array $data = array()
    ) {
        $this->sytelineHelper = $sytelineHelper;
        parent::__construct($context, $customerSession, $checkoutSession, $catalogUrlBuilder, $cartHelper, $httpContext, $itemCollectionFactory, $joinProcessor, $data);
    }

    public function shouldDisplayBackorderWarning()
    {
        $shouldDisplay = false;
        $items = $this->getItems();
        foreach ($items as $item) {
            $product = $item->getProduct();
            $qty = $item->getQty();
            if (!$this->sytelineHelper->isProductAvailable($product, $qty)) {
                $shouldDisplay = true;
                break;
            }
        }

        return $shouldDisplay;
    }
}
