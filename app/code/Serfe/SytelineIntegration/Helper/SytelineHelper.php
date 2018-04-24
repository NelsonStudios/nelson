<?php

namespace Serfe\SytelineIntegration\Helper;

/**
 * Main Syteline Helper
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class SytelineHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SYTELINE_AVAIALABLE_STATUS = 'Available';

    /**
     * Api Helper
     *
     * @var \Serfe\SytelineIntegration\Helper\ApiHelper 
     */
    protected $apiHelper;

    /**
     * Logger
     *
     * @var \Serfe\SytelineIntegration\Logger\Handler 
     */
    protected $logger;
    
    /**
     * Data Transformer
     *
     * @var \Serfe\SytelineIntegration\Helper\TransformData 
     */
    protected $dataTransformHelper;

    /**
     * Submission Helper
     *
     * @var \Serfe\SytelineIntegration\Helper\SubmissionHelper 
     */
    protected $submissionHelper;

    /**
     * Product Repository
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Serfe\SytelineIntegration\Helper\ApiHelper $apiHelper
     * @param \Serfe\SytelineIntegration\Logger\Logger $logger
     * @param \Serfe\SytelineIntegration\Helper\TransformData $dataTransformHelper
     * @param \Serfe\SytelineIntegration\Helper\SubmissionHelper $submissionHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Serfe\SytelineIntegration\Helper\ApiHelper $apiHelper,
        \Serfe\SytelineIntegration\Logger\Logger $logger,
        \Serfe\SytelineIntegration\Helper\TransformData $dataTransformHelper,
        \Serfe\SytelineIntegration\Helper\SubmissionHelper $submissionHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->apiHelper = $apiHelper;
        $this->logger = $logger;
        $this->dataTransformHelper = $dataTransformHelper;
        $this->submissionHelper = $submissionHelper;
        $this->productRepository = $productRepository;

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
        $available = $this->getAvailability($apiResponse);

        return $available;
    }

    /**
     * Get availability attribute from API Response
     *
     * @param array|\stdClass $response
     * @return boolean
     */
    protected function getAvailability($response)
    {
        $available = false;
        if (is_array($response)) {
            // DO LOGGING
        } else {
            if (!$this->responseHasErrors($response)) {
                $available = ($response->ErpGetPartInfoResponse->Availability == $this::SYTELINE_AVAIALABLE_STATUS);
            }
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
     */
    protected function logDataErrors($errors)
    {
        foreach ($errors['errors'] as $error) {
            $this->logger->err($error);
        }
    }

    /**
     * Submit $order data to Syteline line via GetCart Web Service
     *
     * @param \Magento\Sales\Model\Order $order
     * @return boolean
     */
    public function submitCartToSyteline($order)
    {
        try {
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
            $this->submissionHelper->createSubmission($orderData, $apiResponse, $successfullRequest);
        } else {
            $successfullRequest = false;
            $this->logDataErrors($errors);
            $this->submissionHelper->createSubmission($orderData, null, $successfullRequest, $errors);
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
}
