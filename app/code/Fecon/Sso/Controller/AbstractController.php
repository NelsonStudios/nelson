<?php

namespace Fecon\Sso\Controller;

/**
 * Abstract SSO Controller
 */
abstract class AbstractController extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    protected $ssoFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Fecon\Sso\Api\SsoInterfaceFactory $ssoFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ssoFactory = $ssoFactory;
        parent::__construct($context);
    }
}