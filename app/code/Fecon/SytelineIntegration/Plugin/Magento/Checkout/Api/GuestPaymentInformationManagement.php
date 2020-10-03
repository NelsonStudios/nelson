<?php

namespace Fecon\SytelineIntegration\Plugin\Magento\Checkout\Api;

/**
 * Class GuestPaymentInformationManagement
 * @package Fecon\SytelineIntegration\Plugin\Magento\Checkout\Api
 */
class GuestPaymentInformationManagement
{

    /**
     * @var \Fecon\SytelineIntegration\Helper\CheckoutExtraData
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @param \Fecon\SytelineIntegration\Helper\CheckoutExtraData $checkoutHelper
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Fecon\SytelineIntegration\Helper\CheckoutExtraData $checkoutHelper,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @param \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject
     * @param $result
     * @param $cartId
     * @param $email
     * @param $paymentMethod
     * @param null $billingAddress
     * @return mixed
     */
    public function afterSavePaymentInformation(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $result,
        $cartId,
        $email,
        $paymentMethod,
        $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quoteId = $quoteIdMask->getQuoteId();
        $this->checkoutHelper->updateQuoteExtraField($quoteId);
        return $result;
    }
}