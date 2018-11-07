<?php

namespace Fecon\Sso\Model;

/**
 * Sso class
 */
class Sso implements \Fecon\Sso\Api\SsoInterface
{

    protected $metadata;

    public function __construct(
        \Fecon\Sso\Model\SsoMetadata $metadata
    ) {
        $this->metadata = $metadata;
    }

    public function getMetadataXml()
    {
        return $this->metadata->getMetadata();
    }

    
}