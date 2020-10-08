<?php

namespace Fecon\SytelineIntegration\Plugin\Magento\Checkout\Api;

/**
 * @author xuanv
 */
class PaymentInformationManagement
{

    /**
     * @var \Fecon\SytelineIntegration\Helper\CheckoutExtraData
     */
    protected $checkoutHelper;

    /**
     * @param \Fecon\SytelineIntegration\Helper\CheckoutExtraData $checkoutHelper
     */
    public function __construct(
        \Fecon\SytelineIntegration\Helper\CheckoutExtraData $checkoutHelper
    ) {
        $this->checkoutHelper = $checkoutHelper;
    }

    public function afterSavePaymentInformation(
        \Magento\Checkout\Api\PaymentInformationManagementInterface $subject,
        $result,
        $cartId,
        $paymentMethod,
        $billingAddress = null
    ) {
        $this->checkoutHelper->updateQuoteExtraField($cartId);
        return $result;
    }
}
