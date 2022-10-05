<?php
/**
 * @copyright Copyright (c) Shop.Fecon.com, Inc. (http://shop.fecon.com)
 */

namespace Fecon\Sales\Block\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;

class Info extends \Magento\Sales\Block\Order\Info
{
    public function getSytelineCompanyName($_order)
    {
        $sytelineCheckoutExtraFields = $_order->getData("syteline_checkout_extra_fields");
        $sytelineCompanyName = "";

        try {
            $sytelineCheckoutExtra = json_decode($sytelineCheckoutExtraFields);
            $sytelineCompanyName = $sytelineCheckoutExtra->sytelineCompanyName ?? "";
        } catch (\InvalidArgumentException $e) {
            return $sytelineCompanyName;
        }
        return $sytelineCompanyName;
    }
}
