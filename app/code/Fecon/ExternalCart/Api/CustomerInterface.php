<?php
/**
 * Contributor company: Fecon.
 * Contributor Author : <fecon.com>
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
     *  @param  string $username The username to save.
     *  @param  string $password The password to save.
     * @return string $customerToken The token of logged-in customer.
     */
    public function customerLogIn($username,$password);
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
     * @param  string $documotoCustomerUsername The documotoCustomerUsername (email) to search.
     * @return string $customerData
     */
    public function getCustomerByDocumotoUsername($documotoCustomerUsername);
    /**
     * Set the customer address
     *
     * @api
     * @param  string $customerId The customerId to search.
     * @param  string $customerAddressData The customerAddressData to save.
     * @param  string $addressType The addressType to save.
     * @return boolean true on success or flase of failure.
     */
    public function setCustomerAddress($customerData, $customerAddressData, $addressType);
}
