<?php

namespace Fecon\SytelineIntegration\Controller\Price;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Description of Render
 */
class Render extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, JsonFactory $resultJsonFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }
        $result = $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('catalog_product_prices');

        $data = [
            'price_render' => 'product.price.render.default',
            'price_type_code' => 'final_price',
            'zone' => 'item_view',
            'product_id' => $this->getRequest()->getParam('productId')
        ];

        $block = $resultPage->getLayout()
            ->createBlock('Fecon\SytelineIntegration\Pricing\Render', '', ['data' => $data])
            ->toHtml();

        $result->setData(['output' => $block]);
        return $result;
    }
}