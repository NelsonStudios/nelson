<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Model\Api;

use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config extends \Magento\Framework\Model\AbstractModel
{
    const PAYTRACE_VALUT_ENABLE = 'payment/paytrace/paytrace_cc_vault_active';
    const PAYTRACE_RECIPT_EMAIL = 'payment/paytrace/transaction';

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryption;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encription,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
       
        $this->scopeConfig = $scopeConfig;
        $this->_encryption = $encription;
        $this->storeManager  = $storeManager;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $registry);
    }

    /**
     * Get Store config value.
     *
     * @param value $value
     * @return string
     */
    public function getConfigDataValue($value)
    {
        return $this->scopeConfig->getValue(
            $value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is Paytrace payment method enable.
     *
     * @return string
     */
    public function getPaytraceVaultEnable()
    {
        return $this->scopeConfig->getValue(
            self::PAYTRACE_VALUT_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is Customer Email Recipt fpr Paytrace payment method enable.
     *
     * @return string
     */
    public function isReciptEmailEnable()
    {
        return $this->scopeConfig->getValue(
            self::PAYTRACE_RECIPT_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get valid Transection Id.
     *
     * @return string
     */
    public function getValidateTransectionIdString($txnId)
    {
        return str_replace('-capture', '', $txnId);
    }

    /**
     * Get Host name.
     *
     * @return string
     */
    public function getHostName()
    {
        $name = $this->storeManager->getStore()->getBaseUrl();
        return $this->getUrlToDomain($name);
    }

    /**
     * Get Domain from url.
     *
     * @param url $url
     * @return string
     */
    public function getUrlToDomain($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            $host = $url;
        }
        if (substr($host, 0, 4) == "www.") {
            $host = substr($host, 4);
        }
        if (strlen($host) > 50) {
            $host = substr($host, 0, 47) . '...';
        }
            $hostName = explode('.', $host);
        if (count($hostName) == 3) {
            return $hostName[1];
        } elseif (count($hostName) == 2) {
            return $hostName[0];
        }

        return implode('', $hostName);
    }

    /**
     * Get Domain from url.
     *
     * @param url $url
     * @return string
     */
    public function getErrorMessageFromArray($temp)
    {
        if (is_array($temp)) {
            $extractval = reset($temp);
            if (is_array($extractval)) {
                return $this->getErrorMessageFromArray(
                    $extractval
                );
            } else {
                return $extractval;
            }
        } else {
            return $temp;
        }
    }

    /**
     * Encrypt Text.
     *
     * @param text $temp
     * @return string
     */
    public function encryptText($temp)
    {
        return $this->_encryption->encrypt($temp);
    }

    /**
     * Decrypt Text.
     *
     * @param text $temp
     * @return string
     */
    public function decryptText($temp)
    {
        return $this->_encryption->decrypt($temp);
    }

    /**
     * Array converted to Json Text.
     *
     * @param text $temp
     * @param array $array
     * @return string
     */
    public function jsonEncode($temp, $array = false)
    {
        return $this->jsonHelper->jsonEncode($temp);
    }

    /**
     * Json String converted to Array.
     *
     * @param text $temp
     * @param array $array
     * @return string
     */
    public function jsonDecode($temp, $array = false)
    {
        return $this->jsonHelper->jsonDecode($temp);
    }

    /**
     * Get Deploy key.
     *
     * @return string
     */
    public function getDeployKeys()
    {
        return $this->_encryption->exportKeys();
    }

    /**
     * Create Customer Vault from card detail.
     *
     * @param Year $year
     * @param month $month
     * @param Cc number $ccnumber
     * @param customerId $customerId
     * @return string
     */
    public function createCustomerVaultKey(
        $year,
        $month,
        $ccnumber,
        $customerId
    ) {
        $paytraceid = $year.'_'.$month.'_'.$this->getDeployKeys().'_'.$ccnumber.'_'.$customerId;
        return hash('md5', $paytraceid);
    }
}
