<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Model\Api;

use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class Api extends \Elsnertech\Paytrace\Model\Api\Config
{
    const VALUT_URL = 'v1/transactions/authorization/by_customer';
    const VALUT_SALE_URL = 'v1/transactions/sale/by_customer';
    const VOID_URL = 'v1/transactions/void';
    const REFUND_URL = 'v1/transactions/refund/for_transaction';
    const DELETE_CUSTOMER = 'v1/customer/delete';
    const STATUS_BY_ID = 'v1/transactions/export/by_id';
    const PAYTRACE_EMAIL_RECEIPT = 'v1/transactions/email_receipt';
    const PAYTRACE_CAPTURE = 'v1/transactions/authorization/capture';
    const PAYTRACE_CUSTOMER_VAULT_CREATE = 'v1/customer/create';

    /**
     * @var \Elsnertech\Paytrace\Model\Api\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryption;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_object;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_paytraceCollection;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Elsnertech\Paytrace\Model\Api\Config $config,
        \Magento\Framework\Encryption\EncryptorInterface $encription,
        \Elsnertech\Paytrace\Model\ResourceModel\Customers\Collection $paytraceCollection,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Elsnertech\Paytrace\Logger\Logger $loggerCustom,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $interface
    ) {
       
        $this->_config = $config;
        $this->_loggerCustom = $loggerCustom;
        $this->_object = $interface;
        $this->_sessionQuote = $sessionQuote;
        $this->_paytraceCollection = $paytraceCollection;
        $this->_regionFactory = $regionFactory;
        $this->_curl = $curl;
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $encription,
            $jsonHelper,
            $scopeConfig
        );
    }

    /**
     * Store Config value.
     *
     * @param string $value
     * @return string
     */
    public function getConfigData($value)
    {
        $value = 'payment/paytrace/'.$value;
        return $this->getConfigDataValue($value);
    }

    /**
     * Get quote session.
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    protected function _getSession()
    {
        return $this->_sessionQuote;
    }

    /**
     * Get customer id from quote.
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    public function getCustomerId()
    {
        return $this->_getSession()->getCustomerId();
    }

    /**
     * Get customer session.
     *
     * @return Magento\Customer\Model\SessionFactory
     */
    public function getCustomerSession()
    {
        $customerSession = $this->_object->create(
            \Magento\Customer\Model\SessionFactory::class
        )->create();
        return $customerSession;
    }

    /**
     * Make cUrl request.
     *
     * @param Apiurl $apiurl
     * @param Request Param $requestString
     * @param headers $headers
     * @return string
     */
    public function makeApiRequest($apiurl, $requestString, $headers = null)
    {
        $method ='POST';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiurl);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestString);
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        $data = curl_exec($ch);
        $result = $this->jsonDecode($data, true);
        if (curl_error($ch)) {
            $this->_loggerCustom->info(
                'Error while request paytrace payment: '
            );
            $this->_loggerCustom->info(
                var_export(curl_error($ch), true)
            );
            throw new \Magento\Framework\Validator\Exception(
                __("Error while request paytrace payment:".curl_error($ch))
            );
        }

        curl_close($ch);
        unset($ch);
        if (!($result)) {
            $this->_loggerCustom->info(
                'Error while request paytrace payment.'
            );
            throw new \Magento\Framework\Validator\Exception(
                __("Error while request paytrace payment.")
            );
        }

        if (isset($result['error'])) {
            $this->_loggerCustom->info(var_export($result, true));
            $errorMessage = isset($result['error_description'])?
            $result['error_description']:
            $result['error'];
            throw new \Magento\Framework\Validator\Exception(
                __($errorMessage)
            );
        } elseif (isset($result['success']) && $result['success'] == false) {
            $this->_loggerCustom->info(var_export($result, true));
            return $result;
        } else {
            return $result;
        }

        return false;
    }

    /**
     * Get Authorize Token.
     *
     * @return json
     */
    public function getAuthorizeToken()
    {
        $apiurl = $this->getConfigData('apiurl').'oauth/token';
        $username = $this->getConfigData('username');
        $password = $this->decryptText($this->getConfigData('password'));
        $param = [
            'grant_type'=>'password',
            'username'=>$username,
            'password'=>$password
        ];
        $requestString = http_build_query($param);
        $response = $this->makeApiRequest($apiurl, $requestString);
        return $response;
    }

    /**
     * Create API Transection.
     *
     * @param Token $token
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param amount $amount
     * @param Transection Type $type
     * @return json
     */
    public function createTransaction($token, $payment, $amount, $type = 'sale')
    {
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();
        $integratorId = $this->getConfigData('integrator_id');
        $headers = [
          'Authorization: Bearer '.$token,
          'Content-Type: application/json',
          'Cache-Control: no-cache'
        ];
        $street = $billing->getStreet();
        if (is_array($billing->getStreet())) {
            $street = implode(" ", $billing->getStreet());
        }
        
        if ($type=='capture') {
            $apiurl = $this->getConfigData('apiurl').self::PAYTRACE_CAPTURE;
            $param = [
                "transaction_id"=> $payment->getLastTransId(),
                "integrator_id"=>$integratorId
            ];
        } else {
            $state = $this->getStateCode($billing);
            $apiurl = $this->getConfigData('apiurl').'v1/transactions/'.$type.'/keyed';
            $param = [
                'amount'=> $amount,
                "credit_card"=>[
                    "number"=> $payment->getCcNumber(),
                    "expiration_month"=> sprintf('%02d', $payment->getCcExpMonth()),
                    "expiration_year"=> $payment->getCcExpYear()
                ],
                "email"=>$order->getCustomerEmail(),
                "customer_reference_id"=>$order->getIncrementId(),
                "integrator_id"=>$integratorId,
                "csc"=>$payment->getCcCid(),
                "billing_address"=>[
                    "name"=>$billing->getName(),
                    "street_address"=> $street,
                    "city"=>$billing->getCity(),
                    "state"=>$state,
                    "zip"=>$billing->getPostcode()
                ],
                "invoice_id"=>$order->getIncrementId()
            ];
        }

        $requestString = $this->jsonEncode($param, true);
        $response = $this->makeApiRequest($apiurl, $requestString, $headers);
        return $response;
    }

    /**
     * Create Paytrace Customer Profile.
     *
     * @param Elsnertech\Paytrace\Model\Paytrace $paytraceInstance
     * @param PaytraceId $paytraceid
     * @param number $number
     * @param month $month
     * @param year $year
     * @param first try $firsttry
     * @return json
     */
    public function createPaytraceProfile(
        $paytraceInstance,
        $paytraceid,
        $number,
        $month,
        $year,
        $firsttry = false
    ) {
        $token = $this->getAuthorizeToken();
        
        if (isset($token['access_token'])) {
            $integratorId = $this->getConfigData('integrator_id');
            $apiurl = $this->getConfigData('apiurl').self::PAYTRACE_CUSTOMER_VAULT_CREATE;
            $headers = [
              'Authorization: Bearer '.$token['access_token'],
              'Content-Type: application/json',
              'Cache-Control: no-cache'
            ];

            $billing = $paytraceInstance->getInfoInstance()->getOrder()->getBillingAddress();
            $street = $billing->getStreet();
            if (is_array($billing->getStreet())) {
                $street = implode(" ", $billing->getStreet());
            }
            $state = $this->getStateCode($billing);
             $param = [
                "customer_id"=>$paytraceid,
                "credit_card"=>[
                    "number"=> $number,
                    "expiration_month"=> $month,
                    "expiration_year"=> $year
                ],
                "integrator_id"=>$integratorId,
                "billing_address"=>[
                    "name"=>$billing->getName(),
                    "street_address"=> $street,
                    "city"=>$billing->getCity(),
                    "state"=>$state,
                    "zip"=>$billing->getPostcode()
                ]
             ];
             
             $requestString = $this->jsonEncode($param, true);
             $response = $this->makeApiRequest(
                 $apiurl,
                 $requestString,
                 $headers
             );
             return $response;
        } elseif (isset($token['error']) &&
            $token['error'] == 'invalid_grant' &&
            $firsttry
        ) {
            $this->_loggerCustom->info($token['error']);
            return $token['error'];
        } else {
            $this->_loggerCustom->info(
                __('profile Token not found.')
            );
            throw new \Magento\Framework\Validator\Exception(
                __('profile Token not found.')
            );
        }
    }

    /**
     * Return saved card.
     *
     * @return array
     */
    public function getSavedCardArray()
    {
        $collection = $this->getSavedCard();
        $dataArray = [];
        if ($collection !== false) {
            if (!empty($collection)) {
                foreach ($collection as $item) {
                    $dataArray[] = $item->getData();
                }
            }
        }

        return $dataArray;
    }

    /**
     * Create Vault Transection.
     *
     * @param Token $token
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param amount $amount
     * @param Transection Type $type
     * @return array
     */
    public function createValtTransaction($token, $payment, $amount, $type = 'sale')
    {
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();
        $integratorId = $this->getConfigData('integrator_id');
        $additionData = $payment->getAdditionalInformation();
        $customerId = isset($additionData['paytrace_vault'])?$additionData['paytrace_vault']:'';
        $customerId = $this->decryptText($customerId);
        $headers = [
          'Authorization: Bearer '.$token,
          'Content-Type: application/json',
          'Cache-Control: no-cache'
        ];
        $street = $billing->getStreet();
        if (is_array($billing->getStreet())) {
            $street = implode(" ", $billing->getStreet());
        }
        
        if ($type=='capture') {
            $apiurl = $this->getConfigData('apiurl').self::PAYTRACE_CAPTURE;
            $param = [
                "transaction_id"=> $payment->getLastTransId(),
                "integrator_id"=>$integratorId
            ];

        } elseif ($type=='sale') {
            $state = $this->getStateCode($billing);
            $apiurl = $this->getConfigData('apiurl').self::VALUT_SALE_URL;
            $param = [
                'amount'=> $amount,
                "customer_id"=>$customerId ,
                "integrator_id"=>$integratorId,
                "billing_address"=>[
                    "name"=>$billing->getName(),
                    "street_address"=> $street,
                    "city"=>$billing->getCity(),
                    "state"=>$state,
                    "zip"=>$billing->getPostcode()
                ],
                "invoice_id"=>$order->getIncrementId()
            ];
        } else {
            $state = $this->getStateCode($billing);
            $apiurl = $this->getConfigData('apiurl').self::VALUT_URL;
            $param = [
                'amount'=> $amount,
                "customer_id"=>$customerId ,
                "integrator_id"=>$integratorId,
                "billing_address"=>[
                    "name"=>$billing->getName(),
                    "street_address"=> $street,
                    "city"=>$billing->getCity(),
                    "state"=>$state,
                    "zip"=>$billing->getPostcode()
                ],
                "invoice_id"=>$order->getIncrementId()
            ];
        }

        $requestString = $this->jsonEncode($param, true);
        
        $response = $this->makeApiRequest($apiurl, $requestString, $headers);
        return $response;
    }

    /**
     * Get state code from Region.
     *
     * @return string
     */
    public function getStateCode($billing)
    {
        if ($billing->getCountryId() == 'US' ||
            $billing->getCountryId() == 'CA' ||
            $billing->getCountryId() == 'IN'
        ) {
            $region   = $this->_regionFactory->create();
            $regionData = $region->load($billing->getRegionId());
            $code = $regionData->getCode();
            if ($code != '') {
                return $code;
            } else {
                return $billing->getRegion();
            }
        } else {
            return $billing->getRegion();
        }
    }

    /**
     * Create Void Refund transection.
     *
     * @param Token $token
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param amount $amount
     * @return string
     */
    public function createVoidRefundTransaction($token, $payment, $amount)
    {
        if ($payment->getLastTransId()) {
            $integratorId = $this->getConfigData('integrator_id');
            $apiurl = $this->getConfigData('apiurl').self::VOID_URL;
            $headers = [
              'Authorization: Bearer '.$token,
              'Content-Type: application/json',
              'Cache-Control: no-cache'
            ];
             $param = [
                'amount'=> $amount,
                "transaction_id"=>$this->getValidTransectionId($payment->getLastTransId()),
                "integrator_id"=>$integratorId
             ];
             $requestString = $this->jsonEncode($param, true);
             $response = $this->makeApiRequest($apiurl, $requestString, $headers);
             return $response;
        }
    }

    /**
     * Create Refund transection.
     *
     * @param Token $token
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param amount $amount
     * @return string
     */
    public function createRefundTransaction($token, $payment, $amount)
    {
        if ($payment->getLastTransId()) {
            $integratorId = $this->getConfigData('integrator_id');
            $apiurl = $this->getConfigData('apiurl').self::REFUND_URL;
            $headers = [
              'Authorization: Bearer '.$token,
              'Content-Type: application/json',
              'Cache-Control: no-cache'
            ];
             $param = [
                'amount'=> $amount,
                "transaction_id"=>$this->getValidTransectionId($payment->getLastTransId()),
                "integrator_id"=>$integratorId
             ];
             $requestString = $this->jsonEncode($param, true);
             $response = $this->makeApiRequest($apiurl, $requestString, $headers);
             return $response;
        }
    }

    /**
     * Return Saved card.
     *
     * @return string
     */
    public function getSavedCard()
    {
        if ($this->getCustomerSession()->isLoggedIn() ||
            $this->getCustomerId()
        ) {
            $customerId = $this->getCustomerSession()->getCustomer()->getId();
            if ($this->getCustomerId()) {
                $customerId = $this->getCustomerId();
            }

                $collection = $this->_paytraceCollection;
                $collection->addFieldToFilter(
                    'customer_id',
                    ['eq' => $customerId]
                );
            if (!empty($collection)) {
                return $collection;
            }
        }

        return false;
    }

    /**
     * Delete Customer Profile.
     *
     * @param CustomerId $customerId
     * @return string
     */
    public function deleteCustomerProfile($customerId)
    {
        $token = $this->getAuthorizeToken();
        if (isset($token['access_token'])) {
            $integratorId = $this->getConfigData('integrator_id');
            $apiurl = $this->getConfigData('apiurl').self::DELETE_CUSTOMER;
            $headers = [
                  'Authorization: Bearer '.$token['access_token'],
                  'Content-Type: application/json',
                  'Cache-Control: no-cache'
               ];
            $param = [
                    "customer_id"=>$customerId,
                    "integrator_id"=>$integratorId
                ];
            
            $requestString = $this->jsonEncode($param, true);
            $response = $this->makeApiRequest($apiurl, $requestString, $headers);
            return $response;
        }
    }

    /**
     * Get Paytrace Transection Status.
     *
     * @param Transaction Id $transactionId
     * @return string
     */
    public function getStatusByTransecion($transactionId)
    {
        $token = $this->getAuthorizeToken();
        if (isset($token['access_token'])) {
            $integratorId = $this->getConfigData('integrator_id');
            $apiurl = $this->getConfigData('apiurl').self::STATUS_BY_ID;
            $headers = [
                  'Authorization: Bearer '.$token['access_token'],
                  'Content-Type: application/json',
                  'Cache-Control: no-cache'
               ];
            $param = [
            "transaction_id"=> $this->getValidTransectionId($transactionId),
            "integrator_id"=>$integratorId
            ];
            
            $requestString = $this->jsonEncode($param, true);
            $response = $this->makeApiRequest($apiurl, $requestString, $headers);

            return $response;
        }
    }

    /**
     * Send Email Recipt to customer email id.
     *
     * @param transactionId $transactionId
     * @param \Magento\Sales\Model\Order $order
     * @param integratorId $integratorId
     * @param token $token
     * @return string
     */
    public function sendPaytraceEmail(
        $transactionId,
        $order,
        $integratorId,
        $token
    ) {

        $sendTransaction = $this->isReciptEmailEnable();
        
        if (!$sendTransaction) {
            return;
        }
        $token = $this->getAuthorizeToken();
        if (isset($token['access_token'])) {
            $email = $order->getCustomerEmail();
            $integratorId = $this->getConfigData('integrator_id');
            $apiurl = $this->getConfigData('apiurl').self::PAYTRACE_EMAIL_RECEIPT;
            $headers = [
                  'Authorization: Bearer '.$token['access_token'],
                  'Content-Type: application/json',
                  'Cache-Control: no-cache'
               ];
            $param = [
            "transaction_id"=> $this->getValidTransectionId($transactionId),
            "email" => $email,
            "integrator_id"=>$integratorId
            ];
            
            $requestString = $this->jsonEncode($param, true);
            $response = $this->makeApiRequest($apiurl, $requestString, $headers);
            
            if (isset($response["success"]) &&
                $response["success"] &&
                $response['response_code']==149 &&
                $response['status_message']
            ) {
                $order->addStatusHistoryComment(
                    $response['status_message']
                );
            } else {
                if (isset($response["errors"])) {
                    $errormessage = $this->getErrorMessageFromArray(
                        $response["errors"]
                    );
                    $this->_loggerCustom->info(
                        "Paytrace email: ".$errormessage
                    );
                } else {
                    $this->_loggerCustom->info(
                        "Paytrace email receipt error."
                    );
                }
            }
        }
    }

    /**
     * Get Valid Transection id.
     *
     * @return string
     */
    public function getValidTransectionId($txnId)
    {
        return $this->_config->getValidateTransectionIdString($txnId);
    }

    /**
     * Return Host name.
     *
     * @return string
     */
    public function getHostName()
    {
        return $this->_config->getHostName();
        ;
    }
}
