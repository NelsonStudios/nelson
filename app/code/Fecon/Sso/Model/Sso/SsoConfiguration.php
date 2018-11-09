<?php

namespace Fecon\Sso\Model\Sso;

/**
 * Class to handle SimpleSamlPhp configurations
 */
class SsoConfiguration extends \Fecon\Sso\Model\SimpleSaml implements \Fecon\Sso\Api\Sso\SsoConfigurationInterface
{
    public function getInstance()
    {
        $config = \SimpleSAML_Configuration::getInstance();

        return $config;
    }
}