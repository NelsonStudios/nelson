<?php
 
namespace Serfe\AskAnExpert\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CP_CONTACT_ENABLE = 'askanexpert/active_display/enabled_askanexpert';
    const CP_CAPTCHA_ENABLE = 'askanexpert/active_display/enabled_captcha';
    const CP_EMAILUS_ENABLE = 'askanexpert/active_display/enabled_emailus';
    const CP_CATEGORIES_AJAX_URL = 'askanexpert/active_display/category_ajax_url';
    const CP_PRODUCTS_AJAX_URL = 'askanexpert/active_display/products_bycat_ajax_url';
    const CP_SITE_KEY = 'askanexpert/active_display/site_key';
    const CP_SECURE_KEY = 'askanexpert/active_display/secure_key';
    const CP_MAP_ENABLE = 'askanexpert/active_display/enabled_map';
    const CP_MAP_KEY = 'askanexpert/active_display/map_key';
    const CP_META_TITTLE = 'askanexpert/active_display/meta_tittle';
    const CP_META_KEYWORD = 'askanexpert/active_display/meta_keyword';
    const CP_META_DESCRIPTION = 'askanexpert/active_display/meta_description';
    const CP_PAGE_HEADING = 'askanexpert/active_display/contact_heading';
    const CP_PAGE_DESCRIPTION = 'askanexpert/active_display/contact_description';
    const CP_PAGE_LINK = 'askanexpert/active_display/contact_link';
    const PP_POPUP_ENABLE = 'askanexpert/popup_display/enabled_popup';
    const PP_POPUP_POSITION = 'askanexpert/popup_display/popup_view';
    const CF_NAME_TITTLE = 'askanexpert/form_display/name_tittle';
    const CF_EMAIL_TITTLE = 'askanexpert/form_display/email_tittle';
    const CF_PHONE_TITTLE = 'askanexpert/form_display/phone_tittle';
    const CF_SUBJECT_TITTLE = 'askanexpert/form_display/subject_tittle';
    const CF_MESSAGE_TITTLE = 'askanexpert/form_display/message_tittle';
    const CF_BUTTON_TEXT = 'askanexpert/form_display/submitbtn_tittle';
    const XML_PATH_EMAIL_RECIPIENT = 'askanexpert/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'askanexpert/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'askanexpert/email/email_template';
    const XML_PATH_EMAIL_REPLYTEMPLATE = 'askanexpert/email/email_replytemplate';
    const TIME_ZONE = 'general/locale/timezone';

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }
 
    /**
     * [getFrontName description]
     * @return [type] [description]
     */
    public function getFrontName()
    {
        if ($this->isContactEnabled()) {
            if ($this->pagelink()=='') {
                return 'askanexpert/front/index';
            } else {
                return $this->pagelink();
            }
        } else {
            return 'contact';
        }
    }
    /**
     * [timezone description]
     * @return [type] [description]
     */
    public function timezone()
    {
        return $this->scopeConfig->getValue(
            self::TIME_ZONE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [isContactEnabled description]
     * @return boolean [description]
     */
    public function isContactEnabled()
    {
        return $this->scopeConfig->getValue(
            self::CP_CONTACT_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [isCaptchaEnabled description]
     * @return boolean [description]
     */
    public function isCaptchaEnabled()
    {
        return $this->scopeConfig->getValue(
            self::CP_CAPTCHA_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [getsitekey description]
     * @return [type] [description]
     */
    public function getsitekey()
    {
        return $this->scopeConfig->getValue(
            self::CP_SITE_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [getsecurekey description]
     * @return [type] [description]
     */
    public function getsecurekey()
    {
        return $this->scopeConfig->getValue(
            self::CP_SECURE_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [isMapEnabled description]
     * @return boolean [description]
     */
    public function isMapEnabled()
    {
        return $this->scopeConfig->getValue(
            self::CP_MAP_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [getmapkey description]
     * @return [type] [description]
     */
    public function getmapkey()
    {
        return $this->scopeConfig->getValue(
            self::CP_MAP_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [getmapkey description]
     * @return [type] [description]
     */
    public function metatittle()
    {
        return $this->scopeConfig->getValue(
            self::CP_META_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [getmapkey description]
     * @return [type] [description]
     */
    public function metakeyword()
    {
        return $this->scopeConfig->getValue(
            self::CP_META_KEYWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [metadescription description]
     * @return [type] [description]
     */
    public function metadescription()
    {
        return $this->scopeConfig->getValue(
            self::CP_META_DESCRIPTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [metadescription description]
     * @return [type] [description]
     */
    public function pageheading()
    {
        return $this->scopeConfig->getValue(
            self::CP_PAGE_HEADING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [metadescription description]
     * @return [type] [description]
     */
    public function pagedescription()
    {
        return $this->scopeConfig->getValue(
            self::CP_PAGE_DESCRIPTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [pagelink description]
     * @return [type] [description]
     */
    public function pagelink()
    {
        return $this->scopeConfig->getValue(
            self::CP_PAGE_LINK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [pagelink description]
     * @return [type] [description]
     */
    public function isPopupEnabled()
    {
        return $this->scopeConfig->getValue(
            self::PP_POPUP_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [pagelink description]
     * @return [type] [description]
     */
    public function popupposition()
    {
        return $this->scopeConfig->getValue(
            self::PP_POPUP_POSITION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * [nametittle description]
     * @return [type] [description]
     */
    public function nametittle()
    {
        return $this->scopeConfig->getValue(
            self::CF_NAME_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [nametittle description]
     * @return [type] [description]
     */
    public function emailtittle()
    {
        return $this->scopeConfig->getValue(
            self::CF_EMAIL_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [nametittle description]
     * @return [type] [description]
     */
    public function phonetittle()
    {
        return $this->scopeConfig->getValue(
            self::CF_PHONE_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [subjecttittle description]
     * @return [type] [description]
     */
    public function subjecttittle()
    {
        return $this->scopeConfig->getValue(
            self::CF_SUBJECT_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [subjecttittle description]
     * @return [type] [description]
     */
    public function messagetittle()
    {
        return $this->scopeConfig->getValue(
            self::CF_MESSAGE_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [subjecttittle description]
     * @return [type] [description]
     */
    public function buttontext()
    {
        return $this->scopeConfig->getValue(
            self::CF_BUTTON_TEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    ////////////////////////////////////////////////////////////////////////////////
    ///////////////// store information ////////////////////////////////////////
    public function getStoreName()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getstreet1()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/street_line1',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getstreet2()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/street_line2',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getcity()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/city',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getzip()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/postcode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getregion()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/region_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getcountry()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/country_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getphone()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/phone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getstoreemail()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_custom1/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    ////////////////////////////// email //////////////////////////////////////

    public function getreceipt()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_RECIPIENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getemailsender()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getemailtemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getemailreplytemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_REPLYTEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getenableemailus()
    {
        return $this->scopeConfig->getValue(
            self::CP_EMAILUS_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getcategoryajaxurl()
    {
        return $this->scopeConfig->getValue(
            self::CP_CATEGORIES_AJAX_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getproductsbycatajaxurl()
    {
        return $this->scopeConfig->getValue(
            self::CP_PRODUCTS_AJAX_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}