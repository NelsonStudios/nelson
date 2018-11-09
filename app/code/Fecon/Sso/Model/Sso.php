<?php

namespace Fecon\Sso\Model;

/**
 * Sso class
 */
class Sso implements \Fecon\Sso\Api\SsoInterface
{

    protected $metadata;

    public function __construct(
        \Fecon\Sso\Model\Sso\SsoMetadata $metadata
    ) {
        $this->metadata = $metadata;
    }

    public function getMetadataXml()
    {
        return $this->metadata->getMetadata();
    }

    public function handleAuthRequest()
    {
        $this->metadata->getMetaDataConfig();
        $metadata = \SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
//        $metadata = $this->metadata->getMetaDataConfig();
        $idpEntityId = $metadata->getMetaDataCurrentEntityID('saml20-idp-hosted');
//        $idpEntityId = $this->metadata->getIdentityProviderId();
        $idp = \SimpleSAML_IdP::getById('saml2:' . $idpEntityId);
        try {
            \sspmod_saml_IdP_SAML2::receiveAuthnRequest($idp);
        } catch (\Exception $e) {
            if ($e->getMessage() === "Unable to find the current binding.") {
                throw new \SimpleSAML_Error_Error('SSOPARAMS', $e, 400);
            } else {
                throw $e; // do not ignore other exceptions!
            }
        }
        assert(false);
    }
}