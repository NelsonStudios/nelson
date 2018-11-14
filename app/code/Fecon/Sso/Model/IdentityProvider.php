<?php

namespace Fecon\Sso\Model;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * IdentityProvider class
 */
class IdentityProvider implements \Fecon\Sso\Api\IdentityProviderInterface
{

    /**
     * The identifier for this IdP.
     *
     * @var string
     */
    protected $id;

    protected $metadata;

    protected $session;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
    * @var SerializerInterface
    */
    protected $serializer;

    public function __construct(
        \Fecon\Sso\Api\Sso\SsoMetadataInterfaceFactory $metadataFactory,
        \Magento\Customer\Model\Session $session,
        Context $context,
        UrlInterface $urlInterface,
        SerializerInterface $serializer
    ) {
        $this->metadata = $metadataFactory->create();
        $this->id = $this->metadata->getIdentityProviderId();
        $this->session = $session;
        $this->context = $context;
        $this->resultFactory = $context->getResultFactory();
        $this->urlInterface = $urlInterface;
        $this->serializer = $serializer;
    }

    /**
     * Process authentication requests.
     *
     * @param array &$state The authentication request state.
     */
    public function handleAuthenticationRequest(array &$state)
    {
        assert(isset($state['Responder']));

        $state['core:IdP'] = $this->id;

        if (isset($state['SPMetadata']['entityid'])) {
            $spEntityId = $state['SPMetadata']['entityid'];
        } elseif (isset($state['SPMetadata']['entityID'])) {
            $spEntityId = $state['SPMetadata']['entityID'];
        } else {
            $spEntityId = null;
        }
        $state['core:SP'] = $spEntityId;

        // first, check whether we need to authenticate the user
        if (isset($state['ForceAuthn']) && (bool) $state['ForceAuthn']) {
            // force authentication is in effect
            $needAuth = true;
        } else {
            $needAuth = !$this->isAuthenticated();
        }

        $state['IdPMetadata'] = $this->getConfig()->toArray();
        $state['ReturnCallback'] = array('SimpleSAML_IdP', 'postAuth');

//        try {
            if ($needAuth) {
                return $this->authenticate($state);
                assert(false);
            } else {
                $this->reauthenticate($state);
            }
            $this->postAuth($state);
//        } catch (\SimpleSAML_Error_Exception $e) {
//            \SimpleSAML_Auth_State::throwException($state, $e);
//        } catch (\Exception $e) {
//            $e = new \SimpleSAML_Error_UnserializableException($e);
//            \SimpleSAML_Auth_State::throwException($state, $e);
//        }
    }

    /**
     * Is the current user authenticated?
     *
     * @return boolean True if the user is authenticated, false otherwise.
     */
    public function isAuthenticated()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * Authenticate the user.
     *
     * This function authenticates the user.
     *
     * @param array &$state The authentication request state.
     *
     * @throws \SimpleSAML\Module\saml\Error\NoPassive If we were asked to do passive authentication.
     */
    private function authenticate(array &$state)
    {
//        if (isset($state['isPassive']) && (bool) $state['isPassive']) {
//            throw new \SimpleSAML\Module\saml\Error\NoPassive('Passive authentication not supported.');
//        }
//
//        $this->authSource->login($state);
        assert(is_array($state));

        /*
         * Save the identifier of this authentication source, so that we can
         * retrieve it later. This allows us to call the login()-function on
         * the current object.
         */
//        $state[self::AUTHID] = $this->authId;

        // What username we should force, if any
//        if ($this->forcedUsername !== NULL) {
            /*
             * This is accessed by the login form, to determine if the user
             * is allowed to change the username.
             */
//            $state['forcedUsername'] = $this->forcedUsername;
//        }

        // ECP requests supply authentication credentials with the AUthnRequest
        // so we validate them now rather than redirecting
//        if (isset($state['core:auth:username']) && isset($state['core:auth:password'])) {
//            $username = $state['core:auth:username'];
//            $password = $state['core:auth:password'];
//
//            if (isset($state['forcedUsername'])) {
//                $username = $state['forcedUsername'];
//            }
//
//            $attributes = $this->login($username, $password);
//            assert(is_array($attributes));
//            $state['Attributes'] = $attributes;
//
//            return;
//        }

        /* Save the $state-array, so that we can restore it after a redirect. */
//        $id = \SimpleSAML_Auth_State::saveState($state, self::STAGEID);
        $id = $this->saveStateIdToSession($state);

        $url = $this->metadata->getSamlResponseUrl(['AuthState' => $id]);

        // Create login URL
        $loginUrl = $this->urlInterface->getUrl(
            'customer/account/login', 
            [
                'referer' => base64_encode($url)
            ]
        );
        /*
         * Redirect to the login form. We include the identifier of the saved
         * state array as a parameter to the login form.
         */
//        $url = \SimpleSAML\Module::getModuleURL('core/loginuserpass.php');
//        $params = array('AuthState' => $id);
//        \SimpleSAML\Utils\HTTP::redirectTrustedURL($url, $params);

        // Redirect to login URL
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($loginUrl);
        return $resultRedirect;
    }

    /**
     * Retrieve the configuration for this IdP.
     *
     * @return SimpleSAML_Configuration The configuration object.
     */
    public function getConfig()
    {
        return $this->metadata->getMetaDataConfig();
    }

    protected function saveStateIdToSession(&$state, $rawId = false)
    {
        $stateId = \SimpleSAML_Auth_State::getStateId($state, $rawId);
        $serializedState = $this->serializer->serialize($state);
        $this->session->setData($stateId, $serializedState);

        return $stateId;
    }

    /**
     * Retrieves and deletes state from session
     *
     * @param string $stateId
     * @return array|null   Returns null if state does not exist on session
     */
    public function getStateFromSession($stateId)
    {
        $sessionData = $this->session->getData();
        $state = null;
        if (isset($sessionData[$stateId])){
            $serializedState = $sessionData[$stateId];
            $this->removeStateFromSession($stateId);
            $state = $this->serializer->unserialize($serializedState);
        }

        return $state;
    }

    protected function removeStateFromSession($stateId)
    {
        $sessionData = $this->session->getData();
        unset($sessionData[$stateId]);
        $this->session->clearStorage();
        foreach ($sessionData as $key => $value) {
            $this->session->setData($key, $value);
        }
    }

    private function reauthenticate(array &$state)
    {
        return $this->authenticate($state);
    }

    /**
     * The user is authenticated.
     *
     * @param array $state The authentication request state array.
     *
     * @throws SimpleSAML_Error_Exception If we are not authenticated.
     */
    public function postAuth(array $state)
    {

//        if (!$this->isAuthenticated()) {
//            throw new \SimpleSAML_Error_Exception('Not authenticated.');
//        }

        $state['Attributes'] = $this->getAttributes();

        if (isset($state['SPMetadata'])) {
            $spMetadata = $state['SPMetadata'];
        } else {
            $spMetadata = $this->metadata->getSPMetaDataArray();
        }
//
//        if (isset($state['core:SP'])) {
//            $session = \SimpleSAML_Session::getSessionFromRequest();
//            $previousSSOTime = $session->getData('core:idp-ssotime', $state['core:IdP'].';'.$state['core:SP']);
//            if ($previousSSOTime !== null) {
//                $state['PreviousSSOTimestamp'] = $previousSSOTime;
//            }
//        }
//
        $idpMetadata = $this->metadata->getMetaDataConfig()->toArray();
//
//        $pc = new \SimpleSAML_Auth_ProcessingChain($idpMetadata, $spMetadata, 'idp');
//
//        $state['ReturnCall'] = array('SimpleSAML_IdP', 'postAuthProc');
        $state['Destination'] = $spMetadata;
        $state['Source'] = $idpMetadata;

//        $pc->processState($state);

//        self::postAuthProc($state);
        return $this->sendResponse($state);
    }

    protected function getAttributes()
    {
        return [
            'UserName' => ['testusername'],
            'Organization' => ['FECON'],
            'UserGroup' => ['Publisher']
        ];
    }

    /**
     * Send a response to the SP.
     *
     * @param array $state The authentication state.
     */
    public function sendResponse(array $state)
    {
        assert(isset($state['Attributes']));
        assert(isset($state['SPMetadata']));
        assert(isset($state['saml:ConsumerURL']));
        assert(array_key_exists('saml:RequestId', $state)); // Can be NULL
        assert(array_key_exists('saml:RelayState', $state)); // Can be NULL.

        $spMetadata = $state["SPMetadata"];
        $spEntityId = $spMetadata['entityid'];
        $spMetadata = \SimpleSAML_Configuration::loadFromArray(
            $spMetadata,
            '$metadata['.var_export($spEntityId, true).']'
        );

//        SimpleSAML\Logger::info('Sending SAML 2.0 Response to '.var_export($spEntityId, true));

        $requestId = $state['saml:RequestId'];
        $relayState = $state['saml:RelayState'];
        $consumerURL = $state['saml:ConsumerURL'];
        $protocolBinding = $state['saml:Binding'];

//        $idp = \SimpleSAML_IdP::getByState($state);

        $idpMetadata = $this->metadata->getMetaDataConfig();

        $assertion = \sspmod_saml_IdP_SAML2::buildAssertion($idpMetadata, $spMetadata, $state);

        if (isset($state['saml:AuthenticatingAuthority'])) {
            $assertion->setAuthenticatingAuthority($state['saml:AuthenticatingAuthority']);
        }

        // create the session association (for logout)
        $association = array(
            'id'                => 'saml:'.$spEntityId,
            'Handler'           => 'sspmod_saml_IdP_SAML2',
            'Expires'           => $assertion->getSessionNotOnOrAfter(),
            'saml:entityID'     => $spEntityId,
            'saml:NameID'       => $state['saml:idp:NameID'],
            'saml:SessionIndex' => $assertion->getSessionIndex(),
        );

        // maybe encrypt the assertion
        $assertion = \sspmod_saml_IdP_SAML2::encryptAssertion($idpMetadata, $spMetadata, $assertion);

        // create the response
        $ar = \sspmod_saml_IdP_SAML2::buildResponse($idpMetadata, $spMetadata, $consumerURL);
        $ar->setInResponseTo($requestId);
        $ar->setRelayState($relayState);
        $ar->setAssertions(array($assertion));

        // register the session association with the IdP
//        $idp->addAssociation($association);

        $statsData = array(
            'spEntityID'  => $spEntityId,
            'idpEntityID' => $idpMetadata->getString('entityid'),
            'protocol'    => 'saml2',
        );
        if (isset($state['saml:AuthnRequestReceivedAt'])) {
            $statsData['logintime'] = microtime(true) - $state['saml:AuthnRequestReceivedAt'];
        }
//        SimpleSAML_Stats::log('saml:idp:Response', $statsData);

        // send the response
        $binding = \SAML2\Binding::getBinding($protocolBinding);
//        $binding->send($ar);
        return $this->send($ar);
    }

    protected function authenticateInSP($postData)
    {
        
    }

    /**
     * Send a SAML 2 message using the HTTP-POST binding.
     *
     * Note: This function never returns.
     *
     * @param \SAML2\Message $message The message we should send.
     */
    public function send(\SAML2\Message $message)
    {
//        if ($this->destination === null) {
            $destination = $message->getDestination();
//        } else {
//            $destination = $this->destination;
//        }
        $relayState = $message->getRelayState();

        $msgStr = $message->toSignedXML();
        $msgStr = $msgStr->ownerDocument->saveXML($msgStr);

        \SAML2\Utils::getContainer()->debugMessage($msgStr, 'out');

        $msgStr = base64_encode($msgStr);

        if ($message instanceof \SAML2\Request) {
            $msgType = 'SAMLRequest';
        } else {
            $msgType = 'SAMLResponse';
        }

        $post = array();
        $post[$msgType] = $msgStr;
        //@TODO     Get RelayState dynamically
        $post['RelayState'] = 'https://172.18.0.9/simplesaml/module.php/core/authenticate.php?as=default-sp';
        $response = [
            'postData' => $post,
            'destination' => $destination
        ];
    
        return $response;
        if ($relayState !== null) {
            $post['RelayState'] = $relayState;
        }
    }
}