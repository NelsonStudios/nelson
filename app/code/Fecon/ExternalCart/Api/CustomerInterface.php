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
}