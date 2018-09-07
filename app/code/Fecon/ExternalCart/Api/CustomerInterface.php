<?php
/**
 * Contributor company: Fecon.
 * Contributor Author : Bruno <bruno@serfe.com>
 * Date: 2018/08/02
 */
namespace Fecon\ExternalCart\Api;

/**
 * Interface CustomerInterface
 * @api
 */
interface CustomerInterface
{
    /**
     * customerLogIn function to perform customer login using Magento 2 REST API
     * This wrapper will log-in the customer and return the token, also will save information to be
     * used in cart session for already logged-in customer.
     * 
     * @api
     * @return string $customerToken The token of logged-in customer.
     */
    public function customerLogIn();
    /**
     * getCustomerData function to perform customer login using Magento 2 REST API
     * This wrapper will log-in the customer and return the token, also will save information to be
     * used in cart session for already logged-in customer.
     * 
     * @api
     * @return string $customerData The data of logged-in customer.
     */
    public function getCustomerData();
    /**
     * Set the token of the recently created customer
     *
     * @api
     * @param  string $customerId The customerId to save.
     * @return string $customerId
     */
    public function setCustomerToken($customerId);
    /**
     * Get the token of the recently created customer cart
     *
     * @api
     * @return string $token of created customer cart or empty array otherwise.
     */
    public function getCustomerToken();
    /**
     * Get the customer data customer
     *
     * @api
     * @param  string $documotoCustomerId The documotoCustomerId to save.
     * @return string $customerData
     */
    public function getCustomerByDocumotoId($documotoCustomerId);
}