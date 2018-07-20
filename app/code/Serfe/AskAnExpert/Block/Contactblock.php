<?php

namespace  Serfe\AskAnExpert\Block;

use Magento\Framework\View\Element\Template;

class Contactblock extends Template {
    protected $_storeInfo;

    /**
     * Constructor 
     * 
     * @param Template\Context                 $context        
     * @param array                            $data           
     * @param \Serfe\AskAnExpert\Helper\Data   $myModuleHelper 
     * @param \Magento\Store\Model\Information $storeInfo      
     */
    public function __construct(Template\Context $context, array $data = [], \Serfe\AskAnExpert\Helper\Data $myModuleHelper, \Magento\Store\Model\Information $storeInfo) {
        
        parent::__construct($context, $data);
        $this->_mymoduleHelper = $myModuleHelper;
        $this->_storeInfo = $storeInfo;
        $this->_isScopePrivate = true;
    }

    /**
     * _prepareLayout
     * 
     * @return [type]
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if ($this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]) == $this->getUrl('askanexpert/front/index', ['_secure' => true])) {
            $this->pageConfig->getTitle()->set($this->_mymoduleHelper->metatittle());
            $this->pageConfig->setKeywords($this->_mymoduleHelper->metakeyword());
            $this->pageConfig->setDescription($this->_mymoduleHelper->metadescription());
        }
        return $this;
    }

    /**
     * Get action
     * 
     * @return [type] [description]
     */
    public function getFormAction()
    {
        return $this->getUrl('askanexpert/front/save', ['_secure' => true]);
    }
    /**
     * Get action
     * 
     * @return [type] [description]
     */
    public function isimage()
    {
        return $this->_mymoduleHelper->isNewsImageEnabled();
    }
    /**
     * Get action
     * 
     * @return [type] [description]
     */
    public function isContactEnabled()
    {
        return $this->_mymoduleHelper->isContactEnabled();
    }

    /**
     * Get action
     * 
     * @return [type] [description]
     */
    public function isCaptchaEnabled()
    {
         return $this->_mymoduleHelper->isCaptchaEnabled();
    }
    /**
     * Get action
     * 
     * @return [type] [description]
     */
    public function getsitekey()
    {
         return $this->_mymoduleHelper->getsitekey();
    }
    /**
     * Get action
     * 
     * @return [type] [description]
     */
    public function getsecurekey()
    {
         return $this->_mymoduleHelper->getsecurekey();
    }
    /**
     * Get action
     * 
     * @return [type] [description]
     */
    public function isMapEnabled()
    {
         return $this->_mymoduleHelper->isMapEnabled();
    }
    /**
     * Get action
     * 
     * @return [type] [description]
     */
    public function getmapkey()
    {
         return $this->_mymoduleHelper->getmapkey();
    }
    /**
     * Get action
     * 
     * @return [type] [description]
     */
    public function metatittle()
    {
        return $this->_mymoduleHelper->metatittle();
    }
    /**
     * Get metakeyword
     * 
     * @return [type] [description]
     */
    public function metakeyword()
    {
        return $this->_mymoduleHelper->metakeyword();
    }
    /**
     * Get metadescription
     * 
     * @return [type] [description]
     */
    public function metadescription()
    {
        return $this->_mymoduleHelper->metadescription();
    }
    /**
     * Get pageheading
     * 
     * @return [type] [description]
     */
    public function pageheading()
    {
         return $this->_mymoduleHelper->pageheading();
    }
    /**
     * Get pagedescription
     * 
     * @return [type] [description]
     */
    public function pagedescription()
    {
         return $this->_mymoduleHelper->pagedescription();
    }
    /**
     * Get pagelink
     * 
     * @return [type] [description]
     */
    public function pagelink()
    {
         return $this->_mymoduleHelper->pagelink();
    }
    
    ////////////////////////////////////////////////////////////
    /**
     * [isPopupEnabled description]
     * @return boolean [description]
     */
    public function isPopupEnabled()
    {
         return $this->_mymoduleHelper->isPopupEnabled();
    }
    /**
     * [isPopupEnabled description]
     * @return boolean [description]
     */
    public function popupposition()
    {
         return $this->_mymoduleHelper->popupposition();
    }

    ////////////////////////////////////////////////////////////
    /**
     * [isPopupEnabled description]
     * @return boolean [description]
     */
    public function nametittle()
    {
         return $this->_mymoduleHelper->nametittle();
    }
    /**
     * [isPopupEnabled description]
     * @return boolean [description]
     */
    public function emailtittle()
    {
         return $this->_mymoduleHelper->emailtittle();
    }
    /**
     * [isPopupEnabled description]
     * @return boolean [description]
     */
    public function phonetittle()
    {
         return $this->_mymoduleHelper->phonetittle();
    }
    /**
     * [isPopupEnabled description]
     * @return boolean [description]
     */
    public function subjecttittle()
    {
         return $this->_mymoduleHelper->subjecttittle();
    }
    /**
     * [isPopupEnabled description]
     * @return boolean [description]
     */
    public function messagetittle()
    {
         return $this->_mymoduleHelper->messagetittle();
    }
    /**
     * [isPopupEnabled description]
     * @return boolean [description]
     */
    public function buttontext()
    {
         return $this->_mymoduleHelper->buttontext();
    }

    ////////////////////Store information /////////////////////////////////

    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getStoreName()
    {
        return $this->_mymoduleHelper->getStoreName();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getstreet1()
    {
        return $this->_mymoduleHelper->getstreet1();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getstreet2()
    {
        return $this->_mymoduleHelper->getstreet2();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getcity()
    {
        return $this->_mymoduleHelper->getcity();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getzip()
    {
        return $this->_mymoduleHelper->getzip();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getregion()
    {
        return $this->_mymoduleHelper->getregion();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getcountry()
    {
        return $this->_mymoduleHelper->getcountry();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getphone()
    {
        return $this->_mymoduleHelper->getphone();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getstoreemail()
    {
        return $this->_mymoduleHelper->getstoreemail();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getenableemailus()
    {
        return $this->_mymoduleHelper->getenableemailus();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getcategoryajaxurl()
    {
        return $this->_mymoduleHelper->getcategoryajaxurl();
    }
    /**
     * [getStoreName description]
     * @return [type] [description]
     */
    public function getproductsbycatajaxurl()
    {
        return $this->_mymoduleHelper->getproductsbycatajaxurl();
    }
}
