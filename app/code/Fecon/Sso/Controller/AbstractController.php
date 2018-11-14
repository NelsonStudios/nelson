<?php

namespace Fecon\Sso\Controller;

/**
 * Abstract SSO Controller
 */
abstract class AbstractController extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Fecon\Sso\Api\SsoInterfaceFactory
     */
    protected $sso;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Fecon\Sso\Api\SsoInterfaceFactory $ssoFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Fecon\Sso\Api\SsoInterfaceFactory $ssoFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->sso = $ssoFactory->create();
        parent::__construct($context);
    }
}