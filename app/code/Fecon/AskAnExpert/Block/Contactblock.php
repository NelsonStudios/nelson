<?php

namespace  Fecon\AskAnExpert\Block;

use Magento\Framework\View\Element\Template;

class Contactblock extends Template {
    protected $_storeInfo;

    /**
     * Constructor 
     * 
     * @param Template\Context                 $context        
     * @param array                            $data           
     * @param \Fecon\AskAnExpert\Helper\Data   $myModuleHelper 
     * @param \Magento\Store\Model\Information $storeInfo      
     */
    public function __construct(Template\Context $context, array $data = [], \Fecon\AskAnExpert\Helper\Data $myModuleHelper, \Magento\Store\Model\Information $storeInfo) {
        
        parent::__construct($context, $data);
        $this->_mymoduleHelper = $myModuleHelper;
        $this->_storeInfo = $storeInfo;
        $this->_isScopePrivate = true;
    }

    /**
     * _prepareLayout
     * 
     * @return string config value
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
     * @return string config value 
     */
    public function getFormAction()
    {
        return $this->getUrl('askanexpert/front/save', ['_secure' => true]);
    }
    /**
     * Get action
     * 
     * @return string config value 
     */
    public function isimage()
    {
        return $this->_mymoduleHelper->isNewsImageEnabled();
    }
    /**
     * Get action
     * 
     * @return string config value 
     */
    public function isContactEnabled()
    {
        return $this->_mymoduleHelper->isContactEnabled();
    }

    /**
     * Get action
     * 
     * @return string config value 
     */
    public function isCaptchaEnabled()
    {
         return $this->_mymoduleHelper->isCaptchaEnabled();
    }
    /**
     * Get action
     * 
     * @return string config value 
     */
    public function getsitekey()
    {
         return $this->_mymoduleHelper->getsitekey();
    }
    /**
     * Get action
     * 
     * @return string config value 
     */
    public function getsecurekey()
    {
         return $this->_mymoduleHelper->getsecurekey();
    }
    /**
     * Get action
     * 
     * @return string config value 
     */
    public function isMapEnabled()
    {
         return $this->_mymoduleHelper->isMapEnabled();
    }
    /**
     * Get action
     * 
     * @return string config value 
     */
    public function getmapkey()
    {
         return $this->_mymoduleHelper->getmapkey();
    }
    /**
     * Get action
     * 
     * @return string config value 
     */
    public function metatittle()
    {
        return $this->_mymoduleHelper->metatittle();
    }
    /**
     * Get metakeyword
     * 
     * @return string config value 
     */
    public function metakeyword()
    {
        return $this->_mymoduleHelper->metakeyword();
    }
    /**
     * Get metadescription
     * 
     * @return string config value 
     */
    public function metadescription()
    {
        return $this->_mymoduleHelper->metadescription();
    }
    /**
     * Get pageheading
     * 
     * @return string config value 
     */
    public function pageheading()
    {
         return $this->_mymoduleHelper->pageheading();
    }
    /**
     * Get pagedescription
     * 
     * @return string config value 
     */
    public function pagedescription()
    {
         return $this->_mymoduleHelper->pagedescription();
    }
    /**
     * Get pagelink
     * 
     * @return string config value 
     */
    public function pagelink()
    {
         return $this->_mymoduleHelper->pagelink();
    }
    
    /**
     * [isPopupEnabled
     * @return boolean 
     */
    public function isPopupEnabled()
    {
         return $this->_mymoduleHelper->isPopupEnabled();
    }
    /**
     * [popupposition
     * @return boolean 
     */
    public function popupposition()
    {
         return $this->_mymoduleHelper->popupposition();
    }

    /**
     * [nametittle
     * @return boolean 
     */
    public function nametittle()
    {
         return $this->_mymoduleHelper->nametittle();
    }
    /**
     * [emailtittle
     * @return boolean 
     */
    public function emailtittle()
    {
         return $this->_mymoduleHelper->emailtittle();
    }
    /**
     * [phonetittle
     * @return boolean 
     */
    public function phonetittle()
    {
         return $this->_mymoduleHelper->phonetittle();
    }
    /**
     * [subjecttittle
     * @return boolean 
     */
    public function subjecttittle()
    {
         return $this->_mymoduleHelper->subjecttittle();
    }
    /**
     * [messagetittle
     * @return boolean 
     */
    public function messagetittle()
    {
         return $this->_mymoduleHelper->messagetittle();
    }
    /**
     * [buttontext
     * @return boolean 
     */
    public function buttontext()
    {
         return $this->_mymoduleHelper->buttontext();
    }

    ////////////////////Store information /////////////////////////////////

    /**
     * getStoreName
     * @return string config value 
     */
    public function getStoreName()
    {
        return $this->_mymoduleHelper->getStoreName();
    }
    /**
     * getstreet1
     * @return string config value 
     */
    public function getstreet1()
    {
        return $this->_mymoduleHelper->getstreet1();
    }
    /**
     * getstreet2
     * @return string config value 
     */
    public function getstreet2()
    {
        return $this->_mymoduleHelper->getstreet2();
    }
    /**
     * getcity
     * @return string config value 
     */
    public function getcity()
    {
        return $this->_mymoduleHelper->getcity();
    }
    /**
     * getzip
     * @return string config value 
     */
    public function getzip()
    {
        return $this->_mymoduleHelper->getzip();
    }
    /**
     * getregion
     * @return string config value 
     */
    public function getregion()
    {
        return $this->_mymoduleHelper->getregion();
    }
    /**
     * getcountry
     * @return string config value 
     */
    public function getcountry()
    {
        return $this->_mymoduleHelper->getcountry();
    }
    /**
     * getphone
     * @return string config value 
     */
    public function getphone()
    {
        return $this->_mymoduleHelper->getphone();
    }
    /**
     * getstoreemail
     * @return string config value 
     */
    public function getstoreemail()
    {
        return $this->_mymoduleHelper->getstoreemail();
    }
    /**
     * getenableemailus
     * @return string config value 
     */
    public function getenableemailus()
    {
        return $this->_mymoduleHelper->getenableemailus();
    }
    /**
     * getEnableStoreInfo
     * @return string config value
     */
    public function getenablestoreinfo()
    {
        return $this->_mymoduleHelper->getenablestoreinfo();
    }
    /**
     * getcategoryajaxurl
     * @return string config value 
     */
    public function getcategoryajaxurl()
    {
        return $this->_mymoduleHelper->getcategoryajaxurl();
    }
    /**
     * getproductsbycatajaxurl
     * @return string config value 
     */
    public function getproductsbycatajaxurl()
    {
        return $this->_mymoduleHelper->getproductsbycatajaxurl();
    }
}
