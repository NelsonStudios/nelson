<?php

namespace Fecon\Sso\Api;

/**
 * SSO Interface
 */
interface SsoInterface
{

    /**
     * Get IdP Metadata on XML format
     *
     * @return string
     */
    public function getMetadataXml();

    /**
     * Handle Authn request and return the url redirect
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \SimpleSAML_Error_Error
     * @throws \Exception
     */
    public function handleAuthRequest();

    /**
     * Receive an authentication request.
     *
     * @param IdentityProviderInterface $idp The IdP we are receiving it for.
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws SimpleSAML_Error_BadRequest In case an error occurs when trying to receive the request.
     */
    public function receiveAuthnRequest(IdentityProviderInterface $idp);

    /**
     * Retrieves SamlResponse to be sent to the SP
     *
     * @return array
     */
    public function sendSamlResponse();
}