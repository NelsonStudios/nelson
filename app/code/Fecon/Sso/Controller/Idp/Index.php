<?php


namespace Fecon\Sso\Controller\Idp;

class Index extends \Fecon\Sso\Controller\AbstractController
{

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $sso = $this->ssoFactory->create();
        $sso->loadSimpleSamlApplication();
        $sso->getMetadata();
        return $this->resultPageFactory->create();
    }
}
