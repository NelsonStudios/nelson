<?php


namespace Fecon\Sso\Controller\Idp;

/**
 * Controller that returns Saml Reponse after login
 */
class SamlResponse extends \Fecon\Sso\Controller\AbstractController
{

    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}