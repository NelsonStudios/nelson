<?php

namespace Fecon\SytelineIntegration\Helper;

/**
 * Main Syteline Helper
 */
class SytelineHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SYTELINE_AVAIALABLE_STATUS = 'In Stock';

    /**
     * Api Helper
     *
     * @var \Fecon\SytelineIntegration\Helper\ApiHelper 
     */
    protected $apiHelper;

    /**
     * Logger
     *
     * @var \Fecon\SytelineIntegration\Logger\Handler 
     */
    protected $logger;
    
    /**
     * Data Transformer
     *
     * @var \Fecon\SytelineIntegration\Helper\TransformData 
     */
    protected $dataTransformHelper;

    /**
     * Submission Helper
     *
     * @var \Fecon\SytelineIntegration\Helper\SubmissionHelper 
     */
    protected $submissionHelper;

    /**
     * Product Repository
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var EmailHelper
     */
    protected $emailHelper;

    /**
     * @var CacheHelper
     */
    protected $cacheHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Fecon\SytelineIntegration\Helper\ApiHelper $apiHelper
     * @param \Fecon\SytelineIntegration\Logger\Logger $logger
     * @param \Fecon\SytelineIntegration\Helper\TransformData $dataTransformHelper
     * @param \Fecon\SytelineIntegration\Helper\SubmissionHelper $submissionHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param EmailHelper $emailHelper
     * @param CacheHelper $cacheHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Fecon\SytelineIntegration\Helper\ApiHelper $apiHelper,
        \Fecon\SytelineIntegration\Logger\Logger $logger,
        \Fecon\SytelineIntegration\Helper\TransformData $dataTransformHelper,
        \Fecon\SytelineIntegration\Helper\SubmissionHelper $submissionHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        EmailHelper $emailHelper,
        CacheHelper $cacheHelper
    ) {
        $this->apiHelper = $apiHelper;
        $this->logger = $logger;
        $this->dataTransformHelper = $dataTransformHelper;
        $this->submissionHelper = $submissionHelper;
        $this->productRepository = $productRepository;
        $this->emailHelper = $emailHelper;
        $this->cacheHelper = $cacheHelper;

        parent::__construct($context);
    }

    /**
     * Check in Sytline if $product is available
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $qty
     * @return boolean
     */
    public function isProductAvailable(\Magento\Catalog\Model\Product $product, $qty = '1')
    {
        $productData = $this->dataTransformHelper->productToArray($product, $qty);
        $apiResponse = $this->apiHelper->getPartInfo($productData);
        $available = $this->getAvailability($apiResponse, $product->getId());

        return $available;
    }

    /**
     * Get availability attribute from API Response
     *
     * @param array|\stdClass $response
     * @param string $productId
     * @return boolean
     */
    protected function getAvailability($response, $productId)
    {
        $available = false;
        if (is_array($response)) {
            $errors = $response;
        } else {
            if (!$this->responseHasErrors($response, $errors)) {
                $available = ($response->ErpGetPartInfoResponse->Availability == $this::SYTELINE_AVAIALABLE_STATUS);
            }
        }
        if (isset($errors) && !empty($errors)) {
            $this->logDataErrors($errors, null, $productId);
        }

        return $available;
    }

    /**
     * Check if Syteline API Response has errors
     *
     * @param \stdClass $response
     * @param array $errors
     * @return boolean
     */
    protected function responseHasErrors($response, &$errors = [])
    {
        $hasErrors = false;
        if (isset($response->ErpGetPartInfoResponse)) {
            if (!isset($response->ErpGetPartInfoResponse->Availability)) {
                $hasErrors = true;
                $errors['errors'][] = 'It has been an error in the data retrieved by the Web Service GetPartInfo';
            }
        } elseif (isset($response->SubmitCartResponse)) {
            if (!$response->SubmitCartResponse->Success) {
                $hasErrors = true;
                $errors['errors'][] = $response->SubmitCartResponse->Message;
            }
        }

        return $hasErrors;
    }

    /**
     * Log data errors
     *
     * @param array $errors
     * @param null|string $orderId
     * @param null|string $productId
     * @return void
     */
    protected function logDataErrors($errors, $orderId = null, $productId = null)
    {
        foreach ($errors['errors'] as $error) {
            $entity = $orderId ? 'Order' . ' Id: ' . $orderId : 'Product' . ' Id: ' . $productId;

            $this->logger->err($entity . ' - Error: ' . $error);
        }
        $this->emailHelper->sendErrorEmailToAdmin($errors, $orderId, $productId);
    }

    /**
     * Varify and submit $order data to Syteline
     *
     * @param \Magento\Sales\Model\Order $order
     * @return boolean
     */
    public function submitCartToSyteline($order)
    {
        if ($this->canSubmitCart($order)) {
            $successfullRequest = $this->submitCart($order);
        } else {
            $successfullRequest = false;
            $error = 'Order ID: ' . $order->getId() . ' - Cannot submit order to Syteline - Order State: ' . $order->getStatus();
            $this->logger->err($error);
        }

        return $successfullRequest;
    }

    /**
     * Check if $order can be submitted to Syteline
     *
     * @param \Magento\Sales\Model\Order $order
     * @return boolean
     */
    protected function canSubmitCart($order)
    {
        $canSubmitCart = true;
        // If the $order has not been paid
        if ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
            $canSubmitCart = false;
        }

        return $canSubmitCart;
    }

    /**
     * Submit $order data to Syteline line via GetCart Web Service
     *
     * @param \Magento\Sales\Model\Order $order
     * @return boolean
     */
    protected function submitCart($order)
    {
        try {
            $orderId = $order->getId();
            $orderData = $this->dataTransformHelper->orderToArray($order);
            $apiResponse = $this->apiHelper->getCart($orderData);
            $errors = (is_array($apiResponse) && isset($apiResponse['errors'])) ? $apiResponse : false;
        } catch (\Exception $exc) {
            $errors = [
                'errors' => [$exc->getMessage()]
            ];
        }
        if (empty($errors) && !$this->responseHasErrors($apiResponse, $errors)) {
            $successfullRequest = true;
            $this->submissionHelper->createSubmission($orderId, $orderData, $apiResponse, $successfullRequest);
        } else {
            $successfullRequest = false;
            $this->logDataErrors($errors, $orderId);
            $this->submissionHelper->createSubmission($orderId, $orderData, null, $successfullRequest, $errors);
        }

        return $successfullRequest;
    }

    /**
     * Check if $product exists in Syteline
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return boolean
     */
    public function existsInSyteline($product)
    {
        try {
            $loadedProduct = $this->productRepository->getById($product->getId());
            $exists = (bool) $loadedProduct->getExistsInSyteline();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
            $exists = false;
        }

        return $exists;
    }

    /**
     * Check in Syteline the $product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param boolean $specialPrice
     * @param boolean $forceApi     If true will force to get the response from API instead of the cache response
     * @param string $qty
     * @return float|boolean    Returns false if response has no price
     */
    public function getProductPrice(
        \Magento\Catalog\Model\Product $product,
        $specialPrice = false,
        $forceApi = false,
        $qty = '1'
    ) {
        $productData = $this->dataTransformHelper->productToArray($product, $qty);
        $productId = $product->getId();
        $cachePrice = $this->cacheHelper->getPrice($productId, $productData['CustomerId'], $specialPrice);
        if ($cachePrice !== false && $forceApi === false) {
            $price = $cachePrice;
        } else {
            $apiResponse = $this->apiHelper->getPartInfo($productData);
            $price = $this->extractPriceFromResponse($apiResponse, $product->getId(), $specialPrice);
            if ($price !== false) {
                $this->cacheHelper->savePrice($price, $productId, $productData['CustomerId'], $specialPrice);
            }
        }

        return $price;
    }

    /**
     * Get availability attribute from API Response
     *
     * @param array|\stdClass $response
     * @param string $productId
     * @param boolean $specialPrice
     * @return float|boolean    Returns false if response has no price
     */
    protected function extractPriceFromResponse($response, $productId, $specialPrice = false)
    {
        $price = false;
        if (is_array($response)) {
            $errors = $response;
        } else {
            if (!$this->responseHasErrors($response, $errors)) {
                $priceStr = $specialPrice ? $response->ErpGetPartInfoResponse->DiscountedPrice : $response->ErpGetPartInfoResponse->RetailPrice;
                $price = (float) $priceStr;
            }
        }
        if (isset($errors) && !empty($errors)) {
            $this->logDataErrors($errors, null, $productId);
        }

        return $price;
    }
}
