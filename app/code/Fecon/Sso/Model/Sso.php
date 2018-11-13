<?php

namespace Fecon\Sso\Model;

use Fecon\Sso\Api\IdentityProviderInterface;
use Fecon\Sso\Api\IdentityProviderInterfaceFactory;
use Fecon\Sso\Api\Sso\SsoMetadataInterfaceFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Sso class
 */
class Sso implements \Fecon\Sso\Api\SsoInterface
{

    protected $metadata;

    protected $request;

    protected $identityProvider;

    public function __construct(
        SsoMetadataInterfaceFactory $metadataFactory,
        RequestInterface $request,
        IdentityProviderInterfaceFactory $identityProviderFactory
    ) {
        $this->metadata = $metadataFactory->create();
        $this->request = $request;
        $this->identityProvider = $identityProviderFactory->create();
    }

    public function getMetadataXml()
    {
        return $this->metadata->getMetadata();
    }

    public function handleAuthRequest()
    {
        $this->metadata->getMetaDataConfig();
//        $metadata = \SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
//        $metadata = $this->metadata->getMetaDataConfig();
//        $idpEntityId = $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted');
//        $idpEntityId = $this->metadata->getIdentityProviderId();
//        $idp = \SimpleSAML_IdP::getById('saml2:' . $idpEntityId);
        try {
//            \sspmod_saml_IdP_SAML2::receiveAuthnRequest($idp);
            return $this->receiveAuthnRequest($this->identityProvider);
        } catch (\Exception $e) {
            if ($e->getMessage() === "Unable to find the current binding.") {
                throw new \SimpleSAML_Error_Error('SSOPARAMS', $e, 400);
            } else {
                throw $e; // do not ignore other exceptions!
            }
        }
        assert(false);
    }

    /**
     * Receive an authentication request.
     *
     * @param IdentityProviderInterface $idp The IdP we are receiving it for.
     * @throws SimpleSAML_Error_BadRequest In case an error occurs when trying to receive the request.
     */
    public function receiveAuthnRequest(IdentityProviderInterface $idp)
    {

        $metadata = \SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
//        $metadata = $this->metadata->getMetaDataConfig();
        $idpMetadata = $idp->getConfig();

        $supportedBindings = array(\SAML2\Constants::BINDING_HTTP_POST);
        if ($idpMetadata->getBoolean('saml20.sendartifact', false)) {
            $supportedBindings[] = \SAML2\Constants::BINDING_HTTP_ARTIFACT;
        }
        if ($idpMetadata->getBoolean('saml20.hok.assertion', false)) {
            $supportedBindings[] = \SAML2\Constants::BINDING_HOK_SSO;
        }
        if ($idpMetadata->getBoolean('saml20.ecp', false)) {
            $supportedBindings[] = \SAML2\Constants::BINDING_PAOS;
        }

        if (isset($_REQUEST['spentityid'])) {
            /* IdP initiated authentication. */

            if (isset($_REQUEST['cookieTime'])) {
                $cookieTime = (int) $_REQUEST['cookieTime'];
                if ($cookieTime + 5 > time()) {
                    /*
                     * Less than five seconds has passed since we were
                     * here the last time. Cookies are probably disabled.
                     */
                    \SimpleSAML\Utils\HTTP::checkSessionCookie(\SimpleSAML\Utils\HTTP::getSelfURL());
                }
            }

            $spEntityId = (string) $_REQUEST['spentityid'];
            $spMetadata = $metadata->getMetaDataConfig($spEntityId, 'saml20-sp-remote');

            if (isset($_REQUEST['RelayState'])) {
                $relayState = (string) $_REQUEST['RelayState'];
            } else {
                $relayState = null;
            }

            if (isset($_REQUEST['binding'])) {
                $protocolBinding = (string) $_REQUEST['binding'];
            } else {
                $protocolBinding = null;
            }

            if (isset($_REQUEST['NameIDFormat'])) {
                $nameIDFormat = (string) $_REQUEST['NameIDFormat'];
            } else {
                $nameIDFormat = null;
            }

            $requestId = null;
            $IDPList = array();
            $ProxyCount = null;
            $RequesterID = null;
            $forceAuthn = false;
            $isPassive = false;
            $consumerURL = null;
            $consumerIndex = null;
            $extensions = null;
            $allowCreate = true;
            $authnContext = null;
            $binding = null;

            $idpInit = true;

//            SimpleSAML\Logger::info(
//                'SAML2.0 - IdP.SSOService: IdP initiated authentication: '.var_export($spEntityId, true)
//            );
        } else {
            $binding = $this->getCurrentBinding();
            $request = $binding->receive();

            if (!($request instanceof \SAML2\AuthnRequest)) {
                throw new \SimpleSAML_Error_BadRequest(
                    'Message received on authentication request endpoint wasn\'t an authentication request.'
                );
            }

            $spEntityId = $request->getIssuer();
            if ($spEntityId === null) {
                throw new \SimpleSAML_Error_BadRequest(
                    'Received message on authentication request endpoint without issuer.'
                );
            }
            $spMetadata = $metadata->getMetaDataConfig($spEntityId, 'saml20-sp-remote');

            \sspmod_saml_Message::validateMessage($spMetadata, $idpMetadata, $request);

            $relayState = $request->getRelayState();

            $requestId = $request->getId();
            $IDPList = $request->getIDPList();
            $ProxyCount = $request->getProxyCount();
            if ($ProxyCount !== null) {
                $ProxyCount--;
            }
            $RequesterID = $request->getRequesterID();
            $forceAuthn = $request->getForceAuthn();
            $isPassive = $request->getIsPassive();
            $consumerURL = $request->getAssertionConsumerServiceURL();
            $protocolBinding = $request->getProtocolBinding();
            $consumerIndex = $request->getAssertionConsumerServiceIndex();
            $extensions = $request->getExtensions();
            $authnContext = $request->getRequestedAuthnContext();

            $nameIdPolicy = $request->getNameIdPolicy();
            if (isset($nameIdPolicy['Format'])) {
                $nameIDFormat = $nameIdPolicy['Format'];
            } else {
                $nameIDFormat = null;
            }
            if (isset($nameIdPolicy['AllowCreate'])) {
                $allowCreate = $nameIdPolicy['AllowCreate'];
            } else {
                $allowCreate = false;
            }

            $idpInit = false;

//            SimpleSAML\Logger::info(
//                'SAML2.0 - IdP.SSOService: incoming authentication request: '.var_export($spEntityId, true)
//            );
        }

//        SimpleSAML_Stats::log('saml:idp:AuthnRequest', array(
//            'spEntityID'  => $spEntityId,
//            'idpEntityID' => $idpMetadata->getString('entityid'),
//            'forceAuthn'  => $forceAuthn,
//            'isPassive'   => $isPassive,
//            'protocol'    => 'saml2',
//            'idpInit'     => $idpInit,
//        ));

        $acsEndpoint = self::getAssertionConsumerService(
            $supportedBindings,
            $spMetadata,
            $consumerURL,
            $protocolBinding,
            $consumerIndex
        );

        $IDPList = array_unique(array_merge($IDPList, $spMetadata->getArrayizeString('IDPList', array())));
        if ($ProxyCount === null) {
            $ProxyCount = $spMetadata->getInteger('ProxyCount', null);
        }

        if (!$forceAuthn) {
            $forceAuthn = $spMetadata->getBoolean('ForceAuthn', false);
        }

        $sessionLostParams = array(
            'spentityid' => $spEntityId,
            'cookieTime' => time(),
        );
        if ($relayState !== null) {
            $sessionLostParams['RelayState'] = $relayState;
        }

        $sessionLostURL = \SimpleSAML\Utils\HTTP::addURLParameters(
            \SimpleSAML\Utils\HTTP::getSelfURLNoQuery(),
            $sessionLostParams
        );

        $state = array(
            'Responder'                                   => array('sspmod_saml_IdP_SAML2', 'sendResponse'),
            \SimpleSAML_Auth_State::EXCEPTION_HANDLER_FUNC => array('sspmod_saml_IdP_SAML2', 'handleAuthError'),
            \SimpleSAML_Auth_State::RESTART                => $sessionLostURL,

            'SPMetadata'                  => $spMetadata->toArray(),
            'saml:RelayState'             => $relayState,
            'saml:RequestId'              => $requestId,
            'saml:IDPList'                => $IDPList,
            'saml:ProxyCount'             => $ProxyCount,
            'saml:RequesterID'            => $RequesterID,
            'ForceAuthn'                  => $forceAuthn,
            'isPassive'                   => $isPassive,
            'saml:ConsumerURL'            => $acsEndpoint['Location'],
            'saml:Binding'                => $acsEndpoint['Binding'],
            'saml:NameIDFormat'           => $nameIDFormat,
            'saml:AllowCreate'            => $allowCreate,
            'saml:Extensions'             => $extensions,
            'saml:AuthnRequestReceivedAt' => microtime(true),
            'saml:RequestedAuthnContext'  => $authnContext,
        );

        // ECP AuthnRequests need to supply credentials
        if ($binding instanceof SOAP) {
            self::processSOAPAuthnRequest($state);
        }

        return $idp->handleAuthenticationRequest($state);
    }

    /**
     * Find SP AssertionConsumerService based on parameter in AuthnRequest.
     *
     * @param array                    $supportedBindings The bindings we allow for the response.
     * @param SimpleSAML_Configuration $spMetadata The metadata for the SP.
     * @param string|NULL              $AssertionConsumerServiceURL AssertionConsumerServiceURL from request.
     * @param string|NULL              $ProtocolBinding ProtocolBinding from request.
     * @param int|NULL                 $AssertionConsumerServiceIndex AssertionConsumerServiceIndex from request.
     *
     * @return array  Array with the Location and Binding we should use for the response.
     */
    private static function getAssertionConsumerService(
        array $supportedBindings,
        \SimpleSAML_Configuration $spMetadata,
        $AssertionConsumerServiceURL,
        $ProtocolBinding,
        $AssertionConsumerServiceIndex
    ) {
        assert(is_string($AssertionConsumerServiceURL) || $AssertionConsumerServiceURL === null);
        assert(is_string($ProtocolBinding) || $ProtocolBinding === null);
        assert(is_int($AssertionConsumerServiceIndex) || $AssertionConsumerServiceIndex === null);

        /* We want to pick the best matching endpoint in the case where for example
         * only the ProtocolBinding is given. We therefore pick endpoints with the
         * following priority:
         *  1. isDefault="true"
         *  2. isDefault unset
         *  3. isDefault="false"
         */
        $firstNotFalse = null;
        $firstFalse = null;
        foreach ($spMetadata->getEndpoints('AssertionConsumerService') as $ep) {
            if ($AssertionConsumerServiceURL !== null && $ep['Location'] !== $AssertionConsumerServiceURL) {
                continue;
            }
            if ($ProtocolBinding !== null && $ep['Binding'] !== $ProtocolBinding) {
                continue;
            }
            if ($AssertionConsumerServiceIndex !== null && $ep['index'] !== $AssertionConsumerServiceIndex) {
                continue;
            }

            if (!in_array($ep['Binding'], $supportedBindings, true)) {
                /* The endpoint has an unsupported binding. */
                continue;
            }

            // we have an endpoint that matches all our requirements. Check if it is the best one

            if (array_key_exists('isDefault', $ep)) {
                if ($ep['isDefault'] === true) {
                    // this is the first matching endpoint with isDefault set to true
                    return $ep;
                }
                // isDefault is set to FALSE, but the endpoint is still usable
                if ($firstFalse === null) {
                    // this is the first endpoint that we can use
                    $firstFalse = $ep;
                }
            } else {
                if ($firstNotFalse === null) {
                    // this is the first endpoint without isDefault set
                    $firstNotFalse = $ep;
                }
            }
        }

        if ($firstNotFalse !== null) {
            return $firstNotFalse;
        } elseif ($firstFalse !== null) {
            return $firstFalse;
        }

//        SimpleSAML\Logger::warning('Authentication request specifies invalid AssertionConsumerService:');
//        if ($AssertionConsumerServiceURL !== null) {
//            SimpleSAML\Logger::warning('AssertionConsumerServiceURL: '.var_export($AssertionConsumerServiceURL, true));
//        }
//        if ($ProtocolBinding !== null) {
//            SimpleSAML\Logger::warning('ProtocolBinding: '.var_export($ProtocolBinding, true));
//        }
//        if ($AssertionConsumerServiceIndex !== null) {
//            SimpleSAML\Logger::warning(
//                'AssertionConsumerServiceIndex: '.var_export($AssertionConsumerServiceIndex, true)
//            );
//        }

        // we have no good endpoints. Our last resort is to just use the default endpoint
        return $spMetadata->getDefaultEndpoint('AssertionConsumerService', $supportedBindings);
    }

    protected function getCurrentBinding()
    {
//        return new \SAML2\HTTPPost();
        return \SAML2\Binding::getCurrentBinding();
    }
}