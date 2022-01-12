<?php

namespace IWD\Opc\Block;

use Magento\Checkout\Block\Onepage as CheckoutOnepage;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\Module\Manager as ModuleManager;

class Onepage extends CheckoutOnepage
{

    public $checkoutSession;
    protected $moduleManager;
    public $quote = null;

    public function __construct(
        Context                 $context,
        FormKey                 $formKey,
        CompositeConfigProvider $configProvider,
        CheckoutSession         $checkoutSession,
        ModuleManager           $moduleManager,
        array                   $layoutProcessors = [],
        array                   $data = []
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
    }

    public function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    public function amazonLoginEnabled()
    {
        return $this->moduleManager->isEnabled('Amazon_Login');
    }
}
