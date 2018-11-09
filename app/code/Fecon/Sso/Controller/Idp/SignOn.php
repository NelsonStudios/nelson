<?php

namespace Fecon\Sso\Controller\Idp;

/**
 * SignOn Controller
 */
class SignOn extends \Fecon\Sso\Controller\AbstractController
{

    public function execute()
    {
        $sso = $this->ssoFactory->create();
        $sso->handleAuthRequest();
    }
}