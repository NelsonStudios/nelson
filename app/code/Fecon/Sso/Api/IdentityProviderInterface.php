<?php

namespace Fecon\Sso\Api;

/**
 * Identity Provider interface
 */
interface IdentityProviderInterface
{

    const DEFAULT_ORGANIZATION = 'FECON';
    const DEFAULT_USER_GROUP = 'Normal User';

    /**
     * Process authentication requests.
     *
     * @param array &$state The authentication request state.
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function handleAuthenticationRequest(array &$state);

    /**
     * Is the current user authenticated?
     *
     * @return boolean True if the user is authenticated, false otherwise.
     */
    public function isAuthenticated();

    /**
     * Retrieve the configuration for this IdP.
     *
     * @return \SimpleSAML_Configuration The configuration object.
     */
    public function getConfig();

    /**
     * Retrieves and deletes state from session
     *
     * @param string $stateId
     * @return array|null   Returns null if state does not exist on session
     */
    public function getStateFromSession($stateId);

    /**
     * The user is authenticated.
     *
     * @param array $state The authentication request state array.
     * @return array
     * @throws SimpleSAML_Error_Exception If we are not authenticated.
     */
    public function postAuth(array $state);

    /**
     * Send a response to the SP.
     *
     * @param array $state The authentication state.
     * @return array
     */
    public function sendResponse(array $state);

    /**
     * Send a SAML 2 message using the HTTP-POST binding.
     *
     * Note: This function never returns.
     *
     * @param \SAML2\Message $message The message we should send.
     * @return array
     */
    public function send(\SAML2\Message $message);
}