<?php

namespace Fecon\Sso\Controller\Metadata;

use Magento\Framework\Controller\ResultFactory;

/**
 * Controller to generate metadata
 */
class Index extends \Fecon\Sso\Controller\AbstractController
{

    protected $resultFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Fecon\Sso\Api\SsoInterfaceFactory $ssoFactory
    ) {
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context, $resultPageFactory, $ssoFactory);
    }
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $sso = $this->ssoFactory->create();
        $xml = $sso->getMetadataXml();

        return $result->setHeader('Content-Type','text/xml')->setContents($xml);
    }
}