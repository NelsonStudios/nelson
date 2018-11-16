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

    /**
     * @var \Fecon\Sso\Api\Sso\SsoMetadataInterface 
     */
    protected $metadata;

    /**
     * @var \Magento\Customer\Model\Session 
     */
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

    /**
     * Constructor
     *
     * @param \Fecon\Sso\Api\Sso\SsoMetadataInterfaceFactory $metadataFactory
     * @param \Magento\Customer\Model\Session $session
     * @param Context $context
     * @param UrlInterface $urlInterface
     * @param SerializerInterface $serializer
     */
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
     * @return \Magento\Framework\Controller\ResultInterface
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

        if ($needAuth) {
            return $this->authenticate($state);
            assert(false);
        } else {
            return $this->reauthenticate($state);
        }
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
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function authenticate(array &$state)
    {
        assert(is_array($state));

        /* Save the $state-array, so that we can restore it after a redirect. */
        $id = $this->saveStateIdToSession($state);

        return $this->getRedirectUrl($id);
    }

    /**
     * Retrieve the configuration for this IdP.
     *
     * @return \SimpleSAML_Configuration The configuration object.
     */
    public function getConfig()
    {
        return $this->metadata->getMetaDataConfig();
    }

    /**
     * Save $state into session
     *
     * @param array $state
     * @param boolean $rawId
     * @return string
     */
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
        if (isset($sessionData[$stateId])) {
            $serializedState = $sessionData[$stateId];
            $this->removeStateFromSession($stateId);
            $state = $this->serializer->unserialize($serializedState);
        }

        return $state;
    }

    /**
     * Remove state from session
     *
     * @param string $stateId
     * @return void
     */
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
     * @return array
     * @throws SimpleSAML_Error_Exception If we are not authenticated.
     */
    public function postAuth(array $state)
    {
        $state['Attributes'] = $this->getAttributes();

        if (isset($state['SPMetadata'])) {
            $spMetadata = $state['SPMetadata'];
        } else {
            $spMetadata = $this->metadata->getSPMetaDataArray();
        }

        $idpMetadata = $this->metadata->getMetaDataConfig()->toArray();
        $state['Destination'] = $spMetadata;
        $state['Source'] = $idpMetadata;

        return $this->sendResponse($state);
    }

    /**
     * Get user attributes
     *
     * @return array
     */
    protected function getAttributes()
    {
        $userName = $this->getUserName();
        return [
            'UserName' => [$userName],
            'Organization' => [self::DEFAULT_ORGANIZATION],
            'UserGroup' => [self::DEFAULT_USER_GROUP]
        ];
    }

    /**
     * Send a response to the SP.
     *
     * @param array $state The authentication state.
     * @return array
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
                $spMetadata, '$metadata[' . var_export($spEntityId, true) . ']'
        );

        $requestId = $state['saml:RequestId'];
        $relayState = $state['saml:RelayState'];
        $consumerURL = $state['saml:ConsumerURL'];
        $protocolBinding = $state['saml:Binding'];

        $idpMetadata = $this->metadata->getMetaDataConfig();

        $assertion = \sspmod_saml_IdP_SAML2::buildAssertion($idpMetadata, $spMetadata, $state);

        if (isset($state['saml:AuthenticatingAuthority'])) {
            $assertion->setAuthenticatingAuthority($state['saml:AuthenticatingAuthority']);
        }

        // create the session association (for logout)
        $association = array(
            'id' => 'saml:' . $spEntityId,
            'Handler' => 'sspmod_saml_IdP_SAML2',
            'Expires' => $assertion->getSessionNotOnOrAfter(),
            'saml:entityID' => $spEntityId,
            'saml:NameID' => $state['saml:idp:NameID'],
            'saml:SessionIndex' => $assertion->getSessionIndex(),
        );

        // maybe encrypt the assertion
        $assertion = \sspmod_saml_IdP_SAML2::encryptAssertion($idpMetadata, $spMetadata, $assertion);

        // create the response
        $ar = \sspmod_saml_IdP_SAML2::buildResponse($idpMetadata, $spMetadata, $consumerURL);
        $ar->setInResponseTo($requestId);
        $ar->setRelayState($relayState);
        $ar->setAssertions(array($assertion));

        $statsData = array(
            'spEntityID' => $spEntityId,
            'idpEntityID' => $idpMetadata->getString('entityid'),
            'protocol' => 'saml2',
        );
        if (isset($state['saml:AuthnRequestReceivedAt'])) {
            $statsData['logintime'] = microtime(true) - $state['saml:AuthnRequestReceivedAt'];
        }

        return $this->send($ar);
    }

    /**
     * Send a SAML 2 message using the HTTP-POST binding.
     *
     * Note: This function never returns.
     *
     * @param \SAML2\Message $message The message we should send.
     * @return array
     */
    public function send(\SAML2\Message $message)
    {
        $destination = $message->getDestination();

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
        if ($relayState !== null) {
            $post['RelayState'] = $relayState;
        }
        $response = [
            'postData' => $post,
            'destination' => $destination
        ];

        return $response;
    }

    /**
     * Get current logged-in customer's UserName
     *
     * @return string
     */
    protected function getUserName()
    {
        $customer = $this->session->getCustomer();
        $userName = $customer->getData('username');

        return $userName;
    }

    /**
     * Get the redirect url to continue with SSO
     *
     * @param string $stateId
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function getRedirectUrl($stateId)
    {
        $url = $this->metadata->getSamlResponseUrl(['AuthState' => $stateId]);
        if ($this->isAuthenticated()) {
            // Redirect to login URL
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($url);
        } else {
            // Create login URL
            $loginUrl = $this->urlInterface->getUrl(
                'customer/account/login', [
                'referer' => base64_encode($url)
                ]
            );
            // Redirect to login URL
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($loginUrl);
        }

        return $resultRedirect;
    }
}