<?php

namespace Fecon\Sso\Model;

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

    public function __construct(
        \Fecon\Sso\Api\Sso\SsoMetadataInterfaceFactory $metadataFactory
    ) {
        $this->metadata = $metadataFactory->create();
        $this->id = $this->metadata->getIdentityProviderId();
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

        try {
            if ($needAuth) {
                $this->authenticate($state);
                assert(false);
            } else {
                $this->reauthenticate($state);
            }
            $this->postAuth($state);
        } catch (SimpleSAML_Error_Exception $e) {
            SimpleSAML_Auth_State::throwException($state, $e);
        } catch (Exception $e) {
            $e = new SimpleSAML_Error_UnserializableException($e);
            SimpleSAML_Auth_State::throwException($state, $e);
        }
    }
}