<?php

namespace Fecon\Sso\Controller\Idp;

/**
 * SignOn Controller
 */
class SignOn extends \Fecon\Sso\Controller\AbstractController
{

    public function execute()
    {
        return $this->sso->handleAuthRequest();
    }
}