<?php
namespace Brsw\CardConnect\Model;

use Magento\Framework\DataObject;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Inspection\Exception;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class CardConnect extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'cardconnect';
    const ACTION_AUTHORIZE = 'authorize';
    const ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';
    const RESPONSE_CODE_APPROVED = 'Approval';
    const CARDCONNECT_ACTION_AUTH = 'auth';
    const CARDCONNECT_ACTION_CAPTURE = 'capture';
    const CARDCONNECT_ACTION_REFUND = 'refund';

    protected $_code;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $directoryList;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ZendClientFactory $httpClientFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
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
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ZendClientFactory $httpClientFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->httpClientFactory = $httpClientFactory;
        $this->encryptor = $encryptor;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->directoryList = $directoryList;
        $this->_code = self::CODE;
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
    }

    /**
     * Do not validate payment form using server methods
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * Get whether it is possible to capture
     *
     * @return bool
     */
    public function canCapture()
    {
        return true;
    }

    /**
     * Get whether it is possible to refund
     *
     * @return bool
     */
    public function canRefund()
    {
        return true;
    }
    /**
     * Capture payment
     *
     * @param InfoInterface|Payment|Object $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

        $request = $this->_buildPlaceOrderRequest($payment, $amount);
        $response = $this->postRequest($request, self::CARDCONNECT_ACTION_AUTH);
        $this->processErrors($response);
        $this->setTransStatus($payment, $response);
        return $this;
    }

    /**
     * Send capture request to gateway
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($payment->getParentTransactionId()) {
            $request = $this->buildBasicRequest();
            $request['retref'] = $payment->getParentTransactionId();
            $request['authcode'] = $payment->getAdditionalInformation('authcode');
            $request["amount"] = $payment->getAmountOrdered()*100;
            $response = $this->postRequest($request, self::CARDCONNECT_ACTION_CAPTURE);
        } else {
            $request = $request = $this->_buildPlaceOrderRequest($payment, $amount);
            $request['capture'] = 'Y';
            $response = $this->postRequest($request, self::CARDCONNECT_ACTION_AUTH);
        }
        $this->processErrors($response);
        $this->setTransStatus($payment, $response);
        return $this;
    }

    /**
     * Refund capture
     *
     * @param InfoInterface|Payment|Object $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$payment->getParentTransactionId()) {
            return $this;
        }

        $request = $this->buildBasicRequest();
        $request['retref'] = $payment->getParentTransactionId();
        $request['authcode'] = $payment->getAdditionalInformation('authcode');
        $request["amount"] = $amount;
        $request["amount"] = $payment->getAmountOrdered()*100;
        $response = $this->postRequest($request, self::CARDCONNECT_ACTION_REFUND);

        $this->processErrors($response);

        if ($response->getResultCode() == self::RESPONSE_CODE_APPROVED) {
            $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(true);
        }
        return $this;
    }

    /**
     * Return request object with information for 'authorization' or 'sale' action
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return array
     */
    protected function _buildPlaceOrderRequest(\Magento\Payment\Model\InfoInterface $payment)
    {
        $order = $payment->getOrder();
        $request = $this->buildBasicRequest();
        $request["orderid"] = $order->getIncrementId();
        $request["account"] = $payment->getCcNumber();
        $request["expiry"] = sprintf('%02d', $payment->getCcExpMonth()) . substr($payment->getCcExpYear(), -2, 2);
        $request["cvv2"] = $payment->getCcCid();
        $request["amount"] = $payment->getAmountOrdered()*100;
        $request["currency"] = $order->getCurrencyCode();
        $request["name"] = $order->getBillingAddress()->getFirstname()." ".$order->getBillingAddress()->getLastname();
        $street = $order->getBillingAddress()->getStreet();
        $request["address"] = implode(' ', $street);
        $request["city"] = $order->getBillingAddress()->getCity();
        $request["region"] = $order->getBillingAddress()->getRegionCode();
        $request["country"] = $order->getBillingAddress()->getCountryId();
        $request["postal"] = $order->getBillingAddress()->getPostcode();
        $request["ecomind"] = 'E';
        $request["track"] = null;
        $request["tokenize"] = 'Y';

        return $request;
    }

    /**
     * Return basic request array
     *
     * @return array
     */
    protected function buildBasicRequest()
    {
        $requestData = [
            "merchid"=> $this->getConfigData('merchant_id')
        ];
        return $requestData;
    }

    /**
     * If response is failed throw exception
     *
     * @param DataObject $response
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processErrors(DataObject $response)
    {
        if (!$response) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Payment Gateway is unreachable at the moment. Please use another payment option.')
            );
        }
        if ($response->getResptext() != self::RESPONSE_CODE_APPROVED) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Payment Gateway is unreachable at the moment. Please use another payment option.')
            );
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param DataObject $response
     *
     * @return Object
     */
    public function setTransStatus(\Magento\Payment\Model\InfoInterface $payment, DataObject $response)
    {
        switch ($response->getResptext()) {
            case self::RESPONSE_CODE_APPROVED:
                if ($response->getAuthcode()) {
                    $payment->setAdditionalInformation('authcode', $response->getAuthcode());
                }
                $payment->setTransactionId($response->getRetref())->setIsTransactionClosed(0);
                break;
            default:
                break;
        }
        return $payment;
    }

    /**
     * @param array $request
     * @param string $action
     *
     * @return DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function postRequest($request, $action = 'auth')
    {
        $this->log('Action: ' . $action);
        try {
            $this->log('Request');
            $this->log($request);
            $httpHeaders = new \Zend\Http\Headers();
            $token =  $this->getConfigData('merchant_gateway_username').":".
                $this->encryptor->decrypt($this->getConfigData('merchant_gateway_password'));
            $httpHeaders->addHeaders([
                'Authorization' => 'Basic ' . base64_encode($token),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
                ]);
            $_request = new \Zend\Http\Request();
            $_request->setHeaders($httpHeaders);
            $_request->setUri($this->getConfigData('gateway_uri') .'/' . $action);
            $_request->setMethod(\Zend\Http\Request::METHOD_PUT);
            $_request->setContent(json_encode($request));

            $client = new \Zend\Http\Client();
            $options = [
                'adapter'   => 'Zend\Http\Client\Adapter\Curl',
                'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
                'maxredirects' => 5,
                'timeout' => 30
            ];
            $client->setOptions($options);
            $response = $client->send($_request);
            $result = $this->dataObjectFactory->create();
            $content = json_decode($response->getContent());
            $this->log('Response');
            if ($content) {
                $this->log((array)$content);
            } else {
                $this->log($response);
            }
            $result->setData((array) $content);
            return $result;
        } catch (\Zend_Http_Client_Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Payment Gateway is unreachable at the moment. Please use another payment option.')
            );
        }
    }

    /**
     * @param sting | array $data
     * Get Config instance
     */
    protected function log($data)
    {
        if (!$this->getConfigData('debug')) {
            return;
        }
        if (is_array($data)) {
            if (isset($data['account'])) {
                $data['account'] =  substr_replace($data['account'], str_repeat("X", 8), 4, 8);
            }
            $data = print_r($data, 1);
        } elseif (!is_string($data)) {
            $data = (string)$data;
        }

        $logDir = $this->directoryList->getPath(DirectoryList::LOG);
        $writer = new \Zend_Log_Writer_Stream("{$logDir}/cardconnect.log");
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($data);
    }
}
