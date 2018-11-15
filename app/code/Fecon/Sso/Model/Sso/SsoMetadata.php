<?php

namespace Fecon\Sso\Model\Sso;

use SAML2\Constants;
use SimpleSAML\Utils\Crypto as Crypto;
use SimpleSAML\Utils\HTTP as HTTP;
use SimpleSAML\Utils\Config\Metadata as Metadata;

/**
 * Class to handle SSO metadata
 */
class SsoMetadata extends \Fecon\Sso\Model\SimpleSaml implements \Fecon\Sso\Api\Sso\SsoMetadataInterface
{

    /**
     * Return a string representing the signed XML with the IdP metadata
     *
     * @return string The $metadataString with the signature embedded.
     * @throws \Exception If the certificate or private key cannot be loaded, or the metadata doesn't parse properly.
     */
    public function getMetadata()
    {
        if (!$this->applicationInitialized) {
            $this->loadSimpleSamlApplication();
        }
        // load SimpleSAMLphp, configuration and metadata
        $config = \SimpleSAML_Configuration::getInstance();
        $metadata = \SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();

        try {
            $identityProviderId = $this->getIdentityProviderId();
            $idpmeta = $this->getMetaDataConfig();

            $availableCerts = array();

            $keys = array();
            $certInfo = Crypto::loadPublicKey($idpmeta, false, 'new_');
            if ($certInfo !== null) {
                $availableCerts['new_idp.crt'] = $certInfo;
                $keys[] = array(
                    'type'            => 'X509Certificate',
                    'signing'         => true,
                    'encryption'      => true,
                    'X509Certificate' => $certInfo['certData'],
                );
                $hasNewCert = true;
            } else {
                $hasNewCert = false;
            }

            $certInfo = Crypto::loadPublicKey($idpmeta, true);
            $availableCerts['idp.crt'] = $certInfo;
            $keys[] = array(
                'type'            => 'X509Certificate',
                'signing'         => true,
                'encryption'      => ($hasNewCert ? false : true),
                'X509Certificate' => $certInfo['certData'],
            );

            if ($idpmeta->hasValue('https.certificate')) {
                $httpsCert = Crypto::loadPublicKey($idpmeta, true, 'https.');
                assert(isset($httpsCert['certData']));
                $availableCerts['https.crt'] = $httpsCert;
                $keys[] = array(
                    'type'            => 'X509Certificate',
                    'signing'         => true,
                    'encryption'      => false,
                    'X509Certificate' => $httpsCert['certData'],
                );
            }

            $metaArray = array(
                'metadata-set' => 'saml20-idp-remote',
                'entityid'     => $identityProviderId,
            );

            $ssob = $metadata->getGenerated('SingleSignOnServiceBinding', 'saml20-idp-hosted');
            $slob = $metadata->getGenerated('SingleLogoutServiceBinding', 'saml20-idp-hosted');
            $ssol = $this->getSingleSignOnService();
            $slol = $this->getSingleLogoutService();

            if (is_array($ssob)) {
                foreach ($ssob as $binding) {
                    $metaArray['SingleSignOnService'][] = array(
                        'Binding'  => $binding,
                        'Location' => $ssol,
                    );
                }
            } else {
                $metaArray['SingleSignOnService'][] = array(
                    'Binding'  => $ssob,
                    'Location' => $ssol,
                );
            }

            if (is_array($slob)) {
                foreach ($slob as $binding) {
                    $metaArray['SingleLogoutService'][] = array(
                        'Binding'  => $binding,
                        'Location' => $slol,
                    );
                }
            } else {
                $metaArray['SingleLogoutService'][] = array(
                    'Binding'  => $slob,
                    'Location' => $slol,
                );
            }

            if (count($keys) === 1) {
                $metaArray['certData'] = $keys[0]['X509Certificate'];
            } else {
                $metaArray['keys'] = $keys;
            }

            if ($idpmeta->getBoolean('saml20.sendartifact', false)) {
                // Artifact sending enabled
                $metaArray['ArtifactResolutionService'][] = array(
                    'index'    => 0,
                    'Location' => HTTP::getBaseURL().'saml2/idp/ArtifactResolutionService.php',
                    'Binding'  => Constants::BINDING_SOAP,
                );
            }

            if ($idpmeta->getBoolean('saml20.hok.assertion', false)) {
                // Prepend HoK SSO Service endpoint.
                array_unshift($metaArray['SingleSignOnService'], array(
                    'hoksso:ProtocolBinding' => Constants::BINDING_HTTP_REDIRECT,
                    'Binding'                => Constants::BINDING_HOK_SSO,
                    'Location'               => HTTP::getBaseURL().'saml2/idp/SSOService.php'
                ));
            }

            if ($idpmeta->getBoolean('saml20.ecp', false)) {
                $metaArray['SingleSignOnService'][] = array(
                    'index' => 0,
                    'Binding'  => Constants::BINDING_SOAP,
                    'Location' => HTTP::getBaseURL().'saml2/idp/SSOService.php',
                );
            }

            $metaArray['NameIDFormat'] = $idpmeta->getString(
                'NameIDFormat',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'
            );

            if ($idpmeta->hasValue('OrganizationName')) {
                $metaArray['OrganizationName'] = $idpmeta->getLocalizedString('OrganizationName');
                $metaArray['OrganizationDisplayName'] = $idpmeta->getLocalizedString(
                    'OrganizationDisplayName',
                    $metaArray['OrganizationName']
                );

                if (!$idpmeta->hasValue('OrganizationURL')) {
                    throw new SimpleSAML_Error_Exception('If OrganizationName is set, OrganizationURL must also be set.');
                }
                $metaArray['OrganizationURL'] = $idpmeta->getLocalizedString('OrganizationURL');
            }

            if ($idpmeta->hasValue('scope')) {
                $metaArray['scope'] = $idpmeta->getArray('scope');
            }

            if ($idpmeta->hasValue('EntityAttributes')) {
                $metaArray['EntityAttributes'] = $idpmeta->getArray('EntityAttributes');

                // check for entity categories
                if (Metadata::isHiddenFromDiscovery($metaArray)) {
                    $metaArray['hide.from.discovery'] = true;
                }
            }

            if ($idpmeta->hasValue('UIInfo')) {
                $metaArray['UIInfo'] = $idpmeta->getArray('UIInfo');
            }

            if ($idpmeta->hasValue('DiscoHints')) {
                $metaArray['DiscoHints'] = $idpmeta->getArray('DiscoHints');
            }

            if ($idpmeta->hasValue('RegistrationInfo')) {
                $metaArray['RegistrationInfo'] = $idpmeta->getArray('RegistrationInfo');
            }

            if ($idpmeta->hasValue('validate.authnrequest')) {
                $metaArray['sign.authnrequest'] = $idpmeta->getBoolean('validate.authnrequest');
            }

            if ($idpmeta->hasValue('redirect.validate')) {
                $metaArray['redirect.sign'] = $idpmeta->getBoolean('redirect.validate');
            }

            $metaBuilder = new \SimpleSAML_Metadata_SAMLBuilder($identityProviderId);
            $metaBuilder->addMetadataIdP20($metaArray);
            $metaBuilder->addOrganizationInfo($metaArray);

            $metaxml = $metaBuilder->getEntityDescriptorText();

            $metaflat = '$metadata['.var_export($identityProviderId, true).'] = '.var_export($metaArray, true).';';

            // sign the metadata if enabled
            $metaxml = \SimpleSAML_Metadata_Signer::sign($metaxml, $idpmeta->toArray(), 'SAML 2 IdP');

            return $metaxml;
        } catch (\Exception $exception) {
            throw new \SimpleSAML_Error_Error('METADATA', $exception);
        }
    }

    /**
     * Retrieve the metadata as a configuration object.
     *
     * This function will throw an exception if it is unable to locate the metadata.
     *
     * @return \SimpleSAML_Configuration The configuration object representing the metadata.
     * @throws \SimpleSAML_Error_MetadataNotFound If no metadata for the entity specified can be found.
     */
    public function getMetaDataConfig()
    {
        if (!$this->applicationInitialized) {
            $this->loadSimpleSamlApplication();
        }
        $metadata = \SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
        $identityProviderId = $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted');
        $idpmeta = $metadata->getMetaDataConfig($identityProviderId, 'saml20-idp-hosted');
        $idmetaArray = $idpmeta->toArray();
        $idmetaArray['entityid'] = $this->getIdentityProviderId();
        $idmetaArray['metadata-index'] = $this->getIdentityProviderId();
        $idmetaArray['privatekey'] = $this->configHelper->getSslPrivateKey();
        $idmetaArray['certificate'] = $this->configHelper->getSslCertificate();
        $idpmeta = $idpmeta->loadFromArray($idmetaArray);

        return $idpmeta;
    }

    /**
     * getSingleSignOnService
     *
     * @param null|array $routeParams
     * @return string
     */
    public function getSingleSignOnService($routeParams = null)
    {
        return $this->url->getUrl('sso/idp/signon', $routeParams);
    }

    /**
     * getSingleLogoutService
     *
     * @return string
     */
    public function getSingleLogoutService()
    {
        return $this->url->getUrl('sso/idp/logout');
    }

    /**
     * getIdentityProviderId
     *
     * @return string
     */
    public function getIdentityProviderId()
    {
        return $this->url->getUrl('sso/metadata');
    }

    /**
     * getSamlResponseUrl
     *
     * @param null|array $routeParams
     * @return string
     */
    public function getSamlResponseUrl($routeParams = null)
    {
        return $this->url->getUrl('sso/idp/samlresponse', $routeParams);
    }

    /**
     * Returns SP metadata configuration
     *
     * @return \SimpleSAML_Configuration The configuration object.
     */
    public function getSPMetaData()
    {
        if (!$this->applicationInitialized) {
            $this->loadSimpleSamlApplication();
        }
        $config = $this->getSPMetaDataArray();
        $metadata = \SimpleSAML_Configuration::loadFromArray($config);

        return $metadata;
    }

    /**
     * Get formated SP configurations
     *
     * @return array
     */
    public function getSPMetaDataArray()
    {
        $metadata = [];
        $spId = $this->configHelper->getSpEntityId();
        $endpoints = $this->configHelper->getSpEndpoints();
        $publicCertificate = $this->configHelper->getSpPublicCertificate();
        $nameFormatId = $this->configHelper->getSpNameFormatId();
        $validateAuthnReq = $this->configHelper->getSpValidateAuthnReq();
        $samlSignAssrt = $this->configHelper->getSpSamlSignAssertion();

        $metadata[$spId] = [
            'entityid' => $spId,
            'metadata-set' => self::REMOTE_SP_METADATA_SET,
            self::ENDPOINT_LOGOUT_TYPE => $this->getEndpoints(self::ENDPOINT_LOGOUT_TYPE, $endpoints),
            self::ENDPOINT_ASSERTION_TYPE => $this->getEndpoints(self::ENDPOINT_ASSERTION_TYPE, $endpoints),
        ];
        if ($publicCertificate) {
            $metadata[$spId]['keys'] = $this->getKeys($publicCertificate);
        }
        if ($nameFormatId) {
            $metadata[$spId]['NameIDFormat'] = $nameFormatId;
        }
        if ($validateAuthnReq) {
            $metadata[$spId]['validate.authnrequest'] = $validateAuthnReq;
        }
        if ($samlSignAssrt) {
            $metadata[$spId]['saml20.sign.assertion'] = $samlSignAssrt;
        }

        return $metadata;
    }

    /**
     * Get formated endpoints for the $endpointType
     *
     * @param string $endpointType
     * @param array $endpoints
     * @return array
     */
    protected function getEndpoints($endpointType, $endpoints)
    {
        $availableLocations = [];
        foreach ($endpoints as $endpoint) {
            if ($endpointType == $endpoint['endpoint']) {
                $availableLocations[] = $this->getEndpoint($endpoint);
            }
        }

        return $availableLocations;
    }

    /**
     * Get formated endpoint
     *
     * @param array $endpoint
     * @return array
     */
    protected function getEndpoint($endpoint)
    {
        $formatedEndpoint = [];
        if (isset($endpoint['binding']) && !empty($endpoint['binding'])) {
            $formatedEndpoint['Binding'] = $endpoint['binding'];
        }
        if (isset($endpoint['location']) && !empty($endpoint['location'])) {
            $formatedEndpoint['Location'] = $endpoint['location'];
        }
        if (isset($endpoint['index']) && is_numeric($endpoint['index'])) {
            $formatedEndpoint['index'] = (int) $endpoint['index'];
        }
        if (isset($endpoint['is_default']) && !empty($endpoint['is_default'])) {
            $formatedEndpoint['isDefault'] = (bool) $endpoint['is_default'];
        }

        return $formatedEndpoint;
    }

    /**
     * Format keys field based on the configured SP certificate
     *
     * @param string $publicCert
     * @return array
     */
    protected function getKeys($publicCert)
    {
        return [
            [
                'encryption' => false,
                'signing' => true,
                'type' => self::CERTIFICATE_TYPE,
                self::CERTIFICATE_TYPE => $publicCert
            ],
            [
                'encryption' => true,
                'signing' => false,
                'type' => self::CERTIFICATE_TYPE,
                self::CERTIFICATE_TYPE => $publicCert
            ]
        ];
    }
}