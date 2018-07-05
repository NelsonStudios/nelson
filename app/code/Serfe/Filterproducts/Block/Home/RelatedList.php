<?php

namespace Serfe\Filterproducts\Block\Home;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class RelatedList extends \Magento\Catalog\Block\Product\ListProduct {

    protected $_collection;

    protected $categoryRepository;
    
    protected $_resource;
    
    protected $currentProduct;
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context, 
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper, 
        \Magento\Catalog\Model\Layer\Resolver $layerResolver, 
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper, 
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection, 
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        
        $this->currentProduct = $objectManager->get('Magento\Framework\Registry')->registry('current_product');

        $this->categoryRepository = $categoryRepository;
        $this->_collection = $collection;
        $this->_resource = $resource;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    protected function _getProductCollection() {
        return $this->getRelatedProducts();
    }

    public function getRelatedProducts() {
        $count = $this->getProductCount();                       
        $category_id = $this->getData("category_id");
        $collection = $this->currentProduct->getRelatedProductCollection();
        $collection->clear()->getSelect()->reset(\Magento\Framework\DB\Select::WHERE)->reset(\Magento\Framework\DB\Select::ORDER)->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)->reset(\Magento\Framework\DB\Select::GROUP);
        
        if(!$category_id) {
            $category_id = $this->_storeManager->getStore()->getRootCategoryId();
        }
        $category = $this->categoryRepository->get($category_id);
        if(isset($category) && $category) {
            $collection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addUrlRewrite()
                ->addAttributeToFilter('is_saleable', 1, 'left')
                ->addCategoryFilter($category)
                ->addAttributeToSort('created_at','desc');
        } else {
            $collection->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('small_image')
                ->addAttributeToSelect('thumbnail')
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addUrlRewrite()
                ->addAttributeToFilter('is_saleable', 1, 'left')
                ->addAttributeToSort('created_at','desc');
        }
        
        $collection->getSelect()
                ->order('created_at','desc')
                ->limit($count);
                
        return $collection;
    }
    
    public function getLoadedProductCollection() {
        return $this->getRelatedProducts();
    }

    public function getProductCount() {
        $limit = $this->getData("product_count");
        if(!$limit)
            $limit = 10;
        return $limit;
    }
}
