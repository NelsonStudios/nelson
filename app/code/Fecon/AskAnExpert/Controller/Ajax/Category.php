<?php

namespace Fecon\AskAnExpert\Controller\Ajax;

class Category extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $jsonHelper;
    protected $categoryCollection;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->categoryCollection = $categoryCollection;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        if ($this->getRequest()->isAjax()) {
            try {
                $data = $this->getCategoryCollection()->addAttributeToFilter('is_active', array('eq' => '1'));
                return $this->jsonResponse($data->toArray());
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                return $this->jsonResponse($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                return $this->jsonResponse($e->getMessage());
            }
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    /**
     * Returns Category collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected function getCategoryCollection()
    {
        $collection = $this->categoryCollection->create()->addAttributeToSelect('name');

        return $collection;
    }
}
