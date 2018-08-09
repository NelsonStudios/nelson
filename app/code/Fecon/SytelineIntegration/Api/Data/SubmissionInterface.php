<?php


namespace Fecon\SytelineIntegration\Api\Data;

interface SubmissionInterface
{

    const REQUEST = 'request';
    const ERRORS = 'errors';
    const TESTING = 'testing';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const ORDER_ID = 'order_id';
    const SUBMISSION_ID = 'submission_id';
    const ATTEMPTS = 'attempts';
    const SUCCESS = 'success';
    const RESPONSE = 'response';


    /**
     * Get submission_id
     * @return string|null
     */
    public function getSubmissionId();

    /**
     * Set submission_id
     * @param string $submissionId
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setSubmissionId($submissionId);

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setOrderId($orderId);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreationAt();

    /**
     * Set created_at
     * @param string $creationAt
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setCreationAt($creationAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get success
     * @return string|null
     */
    public function getSuccess();

    /**
     * Set success
     * @param string $success
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setSuccess($success);

    /**
     * Get testing
     * @return string|null
     */
    public function getTesting();

    /**
     * Set testing
     * @param string $testing
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setTesting($testing);

    /**
     * Get request
     * @return string|null
     */
    public function getRequest();

    /**
     * Set request
     * @param string $request
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setRequest($request);

    /**
     * Get attempts
     * @return string|null
     */
    public function getAttempts();

    /**
     * Set attempts
     * @param string $attempts
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setAttempts($attempts);

    /**
     * Get response
     * @return string|null
     */
    public function getResponse();

    /**
     * Set response
     * @param string $response
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setResponse($response);

    /**
     * Get errors
     * @return string|null
     */
    public function getErrors();

    /**
     * Set errors
     * @param string $errors
     * @return \Fecon\SytelineIntegration\Api\Data\SubmissionInterface
     */
    public function setErrors($errors);
}
