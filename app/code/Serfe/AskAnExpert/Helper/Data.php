<?php
 
namespace Serfe\AskAnExpert\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CP_CONTACT_ENABLE = 'askanexpert/active_display/enabled_askanexpert';
    const CP_CAPTCHA_ENABLE = 'askanexpert/active_display/enabled_captcha';
    const CP_EMAILUS_ENABLE = 'askanexpert/active_display/enabled_emailus';
    const CP_STOREINFO_ENABLE = 'askanexpert/active_display/enabled_storeinfo';
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
     * getFrontName
     * 
     * @return string $pageLink | 'contact'
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
     * timezone
     * 
     * @return string timezone config value
     */
    public function timezone()
    {
        return $this->scopeConfig->getValue(
            self::TIME_ZONE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * isContactEnabled
     * 
     * @return boolean isContactEnabled config value
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
     * @return boolean isCaptchaEnabled config value
     */
    public function isCaptchaEnabled()
    {
        return $this->scopeConfig->getValue(
            self::CP_CAPTCHA_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getsitekey
     * 
     * @return string getsitekey config value
     */
    public function getsitekey()
    {
        return $this->scopeConfig->getValue(
            self::CP_SITE_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getsecurekey 
     * 
     * @return string getsecurekey config value
     */
    public function getsecurekey()
    {
        return $this->scopeConfig->getValue(
            self::CP_SECURE_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * isMapEnabled
     * 
     * @return boolean isMapEnabled config value
     */
    public function isMapEnabled()
    {
        return $this->scopeConfig->getValue(
            self::CP_MAP_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getmapkey
     * 
     * @return string getmapkey config value
     */
    public function getmapkey()
    {
        return $this->scopeConfig->getValue(
            self::CP_MAP_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * metatittle
     * 
     * @return string metatittle config value
     */
    public function metatittle()
    {
        return $this->scopeConfig->getValue(
            self::CP_META_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * metakeyword
     * 
     * @return string metakeyword config value
     */
    public function metakeyword()
    {
        return $this->scopeConfig->getValue(
            self::CP_META_KEYWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * metadescription
     * 
     * @return string metadescription config value
     */
    public function metadescription()
    {
        return $this->scopeConfig->getValue(
            self::CP_META_DESCRIPTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * pageheading
     * 
     * @return string pageheading config value
     */
    public function pageheading()
    {
        return $this->scopeConfig->getValue(
            self::CP_PAGE_HEADING,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * pagedescription
     * 
     * @return string pagedescription config value
     */
    public function pagedescription()
    {
        return $this->scopeConfig->getValue(
            self::CP_PAGE_DESCRIPTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * pagelink
     * 
     * @return string pagelink config value
     */
    public function pagelink()
    {
        return $this->scopeConfig->getValue(
            self::CP_PAGE_LINK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * isPopupEnabled
     * 
     * @return string isPopupEnabled config value
     */
    public function isPopupEnabled()
    {
        return $this->scopeConfig->getValue(
            self::PP_POPUP_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * popupposition
     * 
     * @return string popupposition config value
     */
    public function popupposition()
    {
        return $this->scopeConfig->getValue(
            self::PP_POPUP_POSITION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * nametittle
     * 
     * @return string nametittle config value
     */
    public function nametittle()
    {
        return $this->scopeConfig->getValue(
            self::CF_NAME_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * emailtittle
     * 
     * @return string emailtittle config value
     */
    public function emailtittle()
    {
        return $this->scopeConfig->getValue(
            self::CF_EMAIL_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * phonetittle
     * 
     * @return string phonetittle config value
     */
    public function phonetittle()
    {
        return $this->scopeConfig->getValue(
            self::CF_PHONE_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * subjecttittle
     * 
     * @return string subjecttittle config value
     */
    public function subjecttittle()
    {
        return $this->scopeConfig->getValue(
            self::CF_SUBJECT_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * messagetittle
     * 
     * @return string messagetittle config value
     */
    public function messagetittle()
    {
        return $this->scopeConfig->getValue(
            self::CF_MESSAGE_TITTLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * buttontext
     * 
     * @return string buttontext config value
     */
    public function buttontext()
    {
        return $this->scopeConfig->getValue(
            self::CF_BUTTON_TEXT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /** Store Information **/

    /**
     * getStoreName
     * 
     * @return string getStoreName config value
     */
    public function getStoreName()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getstreet1
     * 
     * @return string getstreet1 config value
     */
    public function getstreet1()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/street_line1',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getstreet2
     * 
     * @return string getstreet2 config value
     */
    public function getstreet2()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/street_line2',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getcity
     * 
     * @return string getcity config value
     */
    public function getcity()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/city',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getzip
     * 
     * @return string getzip config value
     */
    public function getzip()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/postcode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getregion
     * 
     * @return string getregion config value
     */
    public function getregion()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/region_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getcountry
     * 
     * @return string getcountry config value
     */
    public function getcountry()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/country_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getphone
     * 
     * @return string getphone config value
     */
    public function getphone()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/phone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getstoreemail
     * 
     * @return string getstoreemail config value
     */
    public function getstoreemail()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_custom1/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /** Email Section **/

    /**
     * getreceipt
     * 
     * @return string getreceipt config value
     */
    public function getreceipt()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_RECIPIENT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getemailsender
     * 
     * @return string getemailsender config value
     */
    public function getemailsender()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getemailtemplate
     * 
     * @return string getemailtemplate config value
     */
    public function getemailtemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getemailreplytemplate
     * 
     * @return string getemailreplytemplate config value
     */
    public function getemailreplytemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_REPLYTEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getenableemailus
     * 
     * @return string getenableemailus config value
     */
    public function getenableemailus()
    {
        return $this->scopeConfig->getValue(
            self::CP_EMAILUS_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getenablestoreinfo
     * 
     * @return string getenableemailus config value
     */
    public function getenablestoreinfo()
    {
        return $this->scopeConfig->getValue(
            self::CP_STOREINFO_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getcategoryajaxurl
     * 
     * @return string getcategoryajaxurl config value
     */
    public function getcategoryajaxurl()
    {
        return $this->scopeConfig->getValue(
            self::CP_CATEGORIES_AJAX_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * getproductsbycatajaxurl
     * 
     * @return string getproductsbycatajaxurl config value
     */
    public function getproductsbycatajaxurl()
    {
        return $this->scopeConfig->getValue(
            self::CP_PRODUCTS_AJAX_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}