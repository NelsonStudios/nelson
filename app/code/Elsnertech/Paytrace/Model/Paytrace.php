<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Model;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Payment;

class Paytrace extends \Magento\Payment\Model\Method\Cc
{
    const PAYMENT_METHOD_APYTRACE_CODE = 'paytrace';

    protected $_code = self::PAYMENT_METHOD_APYTRACE_CODE;

    protected $_canAuthorize = true;
    protected $_canCapture = true;

    protected $_isGateway                   = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;

    protected $_supportedCurrencyCodes = ['USD'];
    protected $_formBlockType = \Elsnertech\Paytrace\Block\Form\Paytrace::class;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\Order\Payment\TransactionFactory $transactionFactory,
        \Elsnertech\Paytrace\Model\Api\Api $apiData,
        \Elsnertech\Paytrace\Model\CustomersFactory $paytraceCustomer,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_apiData = $apiData;
        $this->_regionFactory = $regionFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->_paytraceCustomer = $paytraceCustomer;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Validate Paytrace Payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function validate()
    {
        return true;
    }

    /**
     * Authorise Payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(
        \Magento\Payment\Model\InfoInterface $payment,
        $amount
    ) {
        
        try {
            $token = $this->getAuthorizeToken();
            if (isset($token['error']) &&
                $token['error'] == 'invalid_grant'
            ) {
                sleep(2);
                $token = $this->getAuthorizeToken();
            }
            
            if (isset($token['access_token'])) {
                $saledata = $this->_apiData->createTransaction(
                    $token['access_token'],
                    $payment,
                    $amount,
                    'authorization'
                );
                if (isset($saledata["success"]) &&
                    $saledata["success"] &&
                    $saledata['response_code'] == 101 &&
                    $saledata['transaction_id']
                ) {
                    $payment->setTransactionId(
                        $saledata['transaction_id']
                    )->setIsTransactionClosed(0);
                    $payment->setTransactionAdditionalInfo(
                        \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                        $saledata
                    );
                    $this->saveCustomerValut($payment);
                    return $this;
                } else {
                    $this->debugData([$saledata]);
                    if (isset($saledata["errors"])) {
                        $errormessage = $this->getErrorMessageFromArray(
                            $saledata["errors"]
                        );
                        throw new \Magento\Framework\Validator\Exception(
                            __($errormessage)
                        );
                    } elseif (isset($saledata["approval_message"])) {
                        throw new \Magento\Framework\Validator\Exception(
                            __("Payment Error: ".trim($saledata["approval_message"]))
                        );
                    } else {
                        throw new \Magento\Framework\Validator\Exception(
                            __("Payment error.")
                        );
                    }
                }
            } else {
                throw new \Magento\Framework\Validator\Exception(
                    __('authorise Token not found.')
                );
            }

            throw new \Magento\Framework\Validator\Exception(
                __('Payment error.')
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(
                __($e->getMessage())
            );
        }
    }

    /**
     * Capture Payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function capture(
        \Magento\Payment\Model\InfoInterface $payment,
        $amount
    ) {
        try {
            $token = $this->getAuthorizeToken();
            if (isset($token['error']) &&
                $token['error']=='invalid_grant'
            ) {
                sleep(2);
                $token = $this->getAuthorizeToken();
            }
            
            if (isset($token['access_token'])) {
                if ($payment->getLastTransId()) {
                    $transaction = $this->_transactionFactory->create();
                    $transaction->load($payment->getLastTransId(), "txn_id");
                    if ($transaction->getTransactionId() &&
                        $transaction->getTxnType() == 'authorization'
                    ) {
                        $saledata = $this->_apiData->createTransaction(
                            $token['access_token'],
                            $payment,
                            $amount,
                            'capture'
                        );
                        if (isset($saledata["success"]) &&
                            $saledata["success"] &&
                            $saledata['response_code'] == 112 &&
                            $saledata['transaction_id']
                        ) {
                            $payment->setTransactionAdditionalInfo(
                                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                                $saledata
                            );
                        
                            $order = $payment->getOrder();
                            $integratorId = $this->getConfigData('integrator_id');
                            $this->_apiData->sendPaytraceEmail(
                                $saledata['transaction_id'],
                                $order,
                                $integratorId,
                                $token['access_token']
                            );

                            return $this;
                        } else {
                            $this->debugData([$saledata]);
                            if (isset($saledata["errors"])) {
                                $errormessage = $this->getErrorMessageFromArray(
                                    $saledata["errors"]
                                );
                                throw new \Magento\Framework\Validator\Exception(
                                    __($errormessage)
                                );
                            } elseif (isset($saledata["approval_message"])) {
                                throw new \Magento\Framework\Validator\Exception(
                                    __("Payment Error: ".trim($saledata["approval_message"]))
                                );
                            } else {
                                throw new \Magento\Framework\Validator\Exception(
                                    __("Payment error.")
                                );
                            }
                        }
                    }
                } else {
                    $saledata = $this->_apiData->createTransaction(
                        $token['access_token'],
                        $payment,
                        $amount,
                        'sale'
                    );
                    if (isset($saledata["success"]) &&
                        $saledata["success"] &&
                        $saledata['response_code'] == 101 &&
                        $saledata['transaction_id']
                    ) {
                        $payment->setTransactionId(
                            $saledata['transaction_id']
                        )->setIsTransactionClosed(0);
                        $payment->setTransactionAdditionalInfo(
                            \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                            $saledata
                        );
                        
                        $order = $payment->getOrder();
                        $this->saveCustomerValut($payment);
                        $integratorId = $this->getConfigData('integrator_id');
                        $this->_apiData->sendPaytraceEmail(
                            $saledata['transaction_id'],
                            $order,
                            $integratorId,
                            $token['access_token']
                        );

                        return $this;
                    } else {
                        $this->debugData([$saledata]);
                        if (isset($saledata["errors"])) {
                            $errormessage = $this->getErrorMessageFromArray(
                                $saledata["errors"]
                            );
                            throw new \Magento\Framework\Validator\Exception(
                                __($errormessage)
                            );
                        } elseif (isset($saledata["approval_message"])) {
                            throw new \Magento\Framework\Validator\Exception(
                                __("Payment Error: ".trim($saledata["approval_message"]))
                            );
                        } else {
                            throw new \Magento\Framework\Validator\Exception(
                                __("Payment error.")
                            );
                        }
                    }
                }
            } else {
                throw new \Magento\Framework\Validator\Exception(
                    __('capture : Token not found.')
                );
            }

            throw new \Magento\Framework\Validator\Exception(
                __('Payment error.')
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(
                __($e->getMessage())
            );
        }
    }

    /**
     * Refund Payment.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function refund(
        \Magento\Payment\Model\InfoInterface $payment,
        $amount
    ) {

        try {
            $token = $this->getAuthorizeToken();
            if (isset($token['error']) && $token['error'] == 'invalid_grant') {
                sleep(2);
                $token = $this->getAuthorizeToken();
            }

            if (isset($token['access_token'])) {
                $saledata = $this->_apiData->createRefundTransaction(
                    $token['access_token'],
                    $payment,
                    $amount
                );
                $this->debugData([$saledata]);
                if (isset($saledata["success"]) &&
                    $saledata["success"] &&
                    $saledata['response_code'] == 106 &&
                    $saledata['transaction_id']
                ) {
                    $payment->setTransactionId(
                        $saledata['transaction_id']
                    )->setIsTransactionClosed(0);
                    $payment->setTransactionAdditionalInfo(
                        \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                        $saledata
                    );
                    
                    return $this;
                } else {
                    if (isset($saledata["errors"])) {
                        $voidsaledata = $this->_apiData->createVoidRefundTransaction(
                            $token['access_token'],
                            $payment,
                            $amount
                        );
                        $this->debugData([$voidsaledata]);
                        if (isset($voidsaledata["success"]) &&
                            $voidsaledata["success"] &&
                            $voidsaledata['response_code'] == 109 &&
                            $voidsaledata['transaction_id']
                        ) {
                            $payment->setTransactionId(
                                $voidsaledata['transaction_id']
                            )->setIsTransactionClosed(0);
                            $payment->setTransactionAdditionalInfo(
                                \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                                $voidsaledata
                            );
                            return $this;
                        } else {
                            $this->debugData([$voidsaledata]);
                            $errormessage = $this->getErrorMessageFromArray(
                                $voidsaledata["errors"]
                            );
                            throw new \Magento\Framework\Validator\Exception(
                                __($errormessage)
                            );
                        }
                    } else {
                        throw new \Magento\Framework\Validator\Exception(
                            __('Error in refund transaction.')
                        );
                    }
                }
            } else {
                throw new \Magento\Framework\Validator\Exception(
                    __('refund : Token not found.')
                );
            }

            throw new \Magento\Framework\Validator\Exception(
                __('Error in refund transaction.')
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(
                __("refund error:".$e->getMessage())
            );
        }
    }

    /**
     * Get Errors in Array.
     *
     * @param $temp
     * @return $temp
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
     * Get Paytrace Authorize Token.
     *
     * @return json
     */
    public function getAuthorizeToken()
    {
        
        $apiurl = $this->getConfigData('apiurl').'oauth/token';
        $username = $this->getConfigData('username');
        $password = $this->_apiData->decryptText($this->getConfigData('password'));
        $param = [
            'grant_type'=>'password',
            'username'=>$username,
            'password'=>$password
        ];
        $requestString = http_build_query($param);
        try {
            $response = $this->_apiData->makeApiRequest(
                $apiurl,
                $requestString
            );
            return $response;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Validator\Exception(
                __($e->getMessage())
            );
        }
    }

    /**
     * Check whether there are CC types set in configuration
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        if (!$this->getConfigData('integrator_id')) {
            return false;
        }

        return $this->getConfigData(
            'cctypes',
            $quote ? $quote->getStoreId() : null
        ) &&
            parent::isAvailable($quote);
    }

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array(
            $currencyCode,
            $this->_supportedCurrencyCodes
        )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Save Paytrace Valut to Paytrace.
     *
     * @return $this
     */
    public function saveCustomerValut($payment)
    {
        $savedcard = 1;
        if ($this->getInfoInstance()->getOrder()->getCustomerId()) {

            $customerId = $this->getInfoInstance()->getOrder()->getCustomerId();
            $ccnumber = substr($payment->getCcNumber(), -4);
            $month = sprintf('%02d', $payment->getCcExpMonth());
            $year = $payment->getCcExpYear();
            $cid = $payment->getCcCid();
            $cType = $payment->getCcType();
            $host = time();
            $info = $this->getInfoInstance()->getAdditionalInformation();
            $isSaved = isset($info['_data']['is_saved'])?$info['_data']['is_saved']:'';
            $paytraceid = $this->_apiData->createCustomerVaultKey(
                $year,
                $month,
                $ccnumber,
                $customerId
            );
            $paytraceidEncypt = $this->_apiData->encryptText($paytraceid);
            $paytracemodel = $this->_paytraceCustomer->create();
            $paytracemodel->getVaultCardByDetail(
                $paytraceid,
                $customerId,
                $ccnumber,
                $month,
                $year,
                $cType
            );

            if (!$paytracemodel->getEntityId() &&
                $savedcard &&
                $year!='' &&
                $month!='' &&
                $isSaved == 'on'
            ) {
                $savedcard = '';
                $profile = $this->_apiData->createPaytraceProfile(
                    $this,
                    $paytraceid,
                    $payment->getCcNumber(),
                    $month,
                    $year,
                    true
                );
                if ($profile == 'invalid_grant') {
                    sleep(2);
                    $profile = $this->_apiData->createPaytraceProfile(
                        $this,
                        $paytraceid,
                        $payment->getCcNumber(),
                        $month,
                        $year
                    );
                }

                if (isset($profile['response_code']) &&
                    $profile['response_code'] == 160
                ) {
                    if ($isSaved == 'on') {
                        $paytracemodel->setPaytraceCustomerId($paytraceidEncypt);
                        $paytracemodel->setCustomerId($customerId);
                        $paytracemodel->setCcNumber($ccnumber);
                        $paytracemodel->setCcYear($year);
                        $paytracemodel->setCcMonth($month);
                        $paytracemodel->setCcType($cType);
                        $paytracemodel->save();
                    }
                } elseif (isset($profile['status_message']) &&
                    $profile['status_message']
                ) {
                    $errorText = $profile['status_message'];
                    if (isset($profile['errors'])) {
                        foreach ($profile['errors'] as $key => $value) {
                            $errorMessage[] = "CODE-".$key." : ".$value[0];
                        }
                        $errorText = implode(',', $errorMessage);
                    }
                    throw new \Magento\Framework\Validator\Exception(
                        __($errorText)
                    );
                }
            }
        }
    }
    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\DataObject|mixed $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(
        \Magento\Framework\DataObject $data
    ) {
        $additionalData = $data->getData(
            PaymentInterface::KEY_ADDITIONAL_DATA
        );
        
        if (!is_object($additionalData)) {
            $additionalData = new DataObject($additionalData ?: []);
        }

        $savedcard = 1;

        if ($this->getInfoInstance()->getQuote()->getCustomerId()) {
            $customerId = $this->getInfoInstance()->getQuote()->getCustomerId();
            $ccnumber = substr($additionalData->getCcNumber(), -4);
            $month = sprintf('%02d', $additionalData->getCcExpMonth());
            $year = $additionalData->getCcExpYear();
            $cType = $additionalData->getCcType();
            $isSaved = $additionalData->getIsSaved();
            $paytraceid = $this->_apiData->createCustomerVaultKey(
                $year,
                $month,
                $ccnumber,
                $customerId
            );
            $paytraceidEncypt = $this->_apiData->encryptText($paytraceid);
            $paytracemodel = $this->_paytraceCustomer->create();
            $paytracemodel->getVaultCardByDetail(
                $paytraceid,
                $customerId,
                $ccnumber,
                $month,
                $year,
                $cType
            );

            if (!$paytracemodel->getEntityId() &&
                $savedcard &&
                $year!='' &&
                $month!='' &&
                $isSaved == 'on'
            ) {
                $savedcard = '';
            }
        }
        
        foreach ($additionalData as $key => $value) {
            if ($key === \Magento\Framework\Api\ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY) {
                continue;
            }
            $valueArray = $value;
            $valueString = [];
            foreach ($valueArray as $key_data => $valueData) {
                if ($key_data == 'is_saved') {
                    $valueString[$key_data] = $valueData;
                }
            }
            $this->getInfoInstance()->setAdditionalInformation($key, $valueString);
        }
        
        $info = $this->getInfoInstance();
        $info->addData(
            [
                'cc_type' => $additionalData->getCcType(),
                'cc_owner' => $additionalData->getCcOwner(),
                'cc_last_4' => substr($additionalData->getCcNumber(), -4),
                'cc_number' => $additionalData->getCcNumber(),
                'cc_cid' => $additionalData->getCcCid(),
                'cc_exp_month' => $additionalData->getCcExpMonth(),
                'cc_exp_year' => $additionalData->getCcExpYear(),
                'cc_ss_issue' => $additionalData->getCcSsIssue(),
                'cc_ss_start_month' => $additionalData->getCcSsStartMonth(),
                'cc_ss_start_year' => $additionalData->getCcSsStartYear(),
                'paytrace_customer_id' => $savedcard,
                'saved_card' => $savedcard ? 1 : 0,
            ]
        );

        return $this;
    }

    /**
     * Get Paytrace Vault is Enable.
     *
     * @return boolean
     */
    public function getVaultEnable()
    {
        
        if ($this->getInfoInstance()->getQuote()->getCustomerId() &&
            $this->_apiData->getPaytraceVaultEnable()
        ) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Get Payment Block Info.
     *
     * @return \Elsnertech\Paytrace\Block\Info\Paytrace
     */
    public function getInfoBlockType()
    {
        return \Elsnertech\Paytrace\Block\Info\Paytrace::class;
    }

    /**
     * Fetch Transaction Detail info.
     *
     * @return array
     */
    public function fetchTransactionDetailInfo($payment, $txnId)
    {
        $transaction = $this->_transactionFactory->create();
        $transaction->load($txnId, "txn_id");
        $data = [];
        if ($transaction->getAdditionalInformation() != '') {
            $infodata = $transaction->getAdditionalInformation();
            $data = isset($infodata[\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS])?
            $infodata[\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS]:
            [];
        }
        return $data;
    }
}
