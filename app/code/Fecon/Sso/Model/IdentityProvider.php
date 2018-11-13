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
    private $serializer;

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
}