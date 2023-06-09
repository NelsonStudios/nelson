<?php
namespace DevPhase\CategoryMenuBlocksWidget\Block\Widget;

class CategoryMenuBlocksWidget extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'widget/categorymenublockswidget.phtml';

    const DEFAULT_IMAGE_WIDTH = 250;
    const DEFAULT_IMAGE_HEIGHT = 250;
    
    /**
    * \Magento\Catalog\Model\CategoryFactory $categoryFactory
    */
    protected $_categoryFactory;
    
    /**
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
    * @param array $data
    */
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->_categoryFactory = $categoryFactory;
        parent::__construct($context);
    }

    /**
    * Retrieve current store categories
    *
    * @return \Magento\Framework\Data\Tree\Node\Collection|\Magento\Catalog\Model\Resource\Category\Collection|array
    */
    public function getCategoryCollection()
    {
        $categoriesCollection = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect('*')->addAttributeToFilter('include_in_menu',1)->addAttributeToFilter('is_active',1)->addAttributeToFilter('level',2);
        
        return $categoriesCollection;
    }
    
    public function getCategoryImage($cat_id) {
        $category = $this->_categoryFactory->create();
        $category->load($cat_id);
        $cat_img = $category->getImageUrl();
        return $cat_img;
		}
    /**
    * Get the width of product image
    * @return int
    */
    public function getImageWidth() {
        if($this->getData('imagewidth')==''){
            return DEFAULT_IMAGE_WIDTH;
        }
        return (int) $this->getData('imagewidth');
    }

    /**
    * Get the height of product image
    * @return int
    */
    public function getImageHeight() {
        if($this->getData('imageheight')==''){
            return DEFAULT_IMAGE_HEIGHT;
        }
        return (int) $this->getData('imageheight');
    }
    
    public function canShowImage(){
        if($this->getData('image') == 'image')
            return true;
        elseif($this->getData('image') == 'no-image')
            return false;
    }
}