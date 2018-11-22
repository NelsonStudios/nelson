<?php

namespace Fecon\Sso\Api\Sso;

/**
 * Metadata interface
 */
interface SsoMetadataInterface
{

    const REMOTE_SP_METADATA_SET = 'saml20-sp-remote';
    const ENDPOINT_LOGOUT_TYPE = 'SingleLogoutService';
    const ENDPOINT_ASSERTION_TYPE = 'AssertionConsumerService';
    const CERTIFICATE_TYPE = 'X509Certificate';

    /**
     * Return a string representing the signed XML with the IdP metadata
     *
     *
     * @return string The $metadataString with the signature embedded.
     * @throws \Exception If the certificate or private key cannot be loaded, or the metadata doesn't parse properly.
     */
    public function getMetadata();

    /**
     * Retrieve the metadata as a configuration object.
     *
     * This function will throw an exception if it is unable to locate the metadata.
     *
     * @return \SimpleSAML_Configuration The configuration object representing the metadata.
     * @throws \SimpleSAML_Error_MetadataNotFound If no metadata for the entity specified can be found.
     */
    public function getMetaDataConfig();

    /**
     * getSingleSignOnService
     *
     * @param null|array $routeParams
     * @return string
     */
    public function getSingleSignOnService($routeParams = null);

    /**
     * getSingleLogoutService
     *
     * @return string
     */
    public function getSingleLogoutService();

    /**
     * getIdentityProviderId
     *
     * @return string
     */
    public function getIdentityProviderId();

    /**
     * getSamlResponseUrl
     *
     * @param null|array $routeParams
     * @return string
     */
    public function getSamlResponseUrl($routeParams = null);

    /**
     * Returns SP metadata configuration
     *
     * @return \SimpleSAML_Configuration The configuration object.
     */
    public function getSPMetaData();

    /**
     * Get formated SP configurations
     *
     * @return array
     */
    public function getSPMetaDataArray();
}