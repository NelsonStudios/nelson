<?php


namespace Serfe\SytelineIntegration\Model;

use Serfe\SytelineIntegration\Api\Data\SubmissionInterface;

class Submission extends \Magento\Framework\Model\AbstractModel implements SubmissionInterface
{

    protected $_eventPrefix = 'serfe_sytelineintegration_submission';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Serfe\SytelineIntegration\Model\ResourceModel\Submission');
    }

    /**
     * Get submission_id
     * @return string
     */
    public function getSubmissionId()
    {
        return $this->getData(self::SUBMISSION_ID);
    }

    /**
     * Set submission_id
     * @param string $submissionId
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setSubmissionId($submissionId)
    {
        return $this->setData(self::SUBMISSION_ID, $submissionId);
    }

    /**
     * Get order_id
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set order_id
     * @param string $orderId
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get created_at
     * @return string
     */
    public function getCreationAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $creationAt
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setCreationAt($creationAt)
    {
        return $this->setData(self::CREATED_AT, $creationAt);
    }

    /**
     * Get updated_at
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get success
     * @return string
     */
    public function getSuccess()
    {
        return $this->getData(self::SUCCESS);
    }

    /**
     * Set success
     * @param string $success
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setSuccess($success)
    {
        return $this->setData(self::SUCCESS, $success);
    }

    /**
     * Get testing
     * @return string
     */
    public function getTesting()
    {
        return $this->getData(self::TESTING);
    }

    /**
     * Set testing
     * @param string $testing
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setTesting($testing)
    {
        return $this->setData(self::TESTING, $testing);
    }

    /**
     * Get request
     * @return string
     */
    public function getRequest()
    {
        return $this->getData(self::REQUEST);
    }

    /**
     * Set request
     * @param string $request
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setRequest($request)
    {
        return $this->setData(self::REQUEST, $request);
    }

    /**
     * Get attempts
     * @return string
     */
    public function getAttempts()
    {
        return $this->getData(self::ATTEMPTS);
    }

    /**
     * Set attempts
     * @param string $attempts
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setAttempts($attempts)
    {
        return $this->setData(self::ATTEMPTS, $attempts);
    }

    /**
     * Get response
     * @return string
     */
    public function getResponse()
    {
        return $this->getData(self::RESPONSE);
    }

    /**
     * Set response
     * @param string $response
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setResponse($response)
    {
        return $this->setData(self::RESPONSE, $response);
    }

    /**
     * Get errors
     * @return string
     */
    public function getErrors()
    {
        return $this->getData(self::ERRORS);
    }

    /**
     * Set errors
     * @param string $errors
     * @return \Serfe\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setErrors($errors)
    {
        return $this->setData(self::ERRORS, $errors);
    }
}
