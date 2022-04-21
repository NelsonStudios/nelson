<?php
/**
 * @copyright Copyright (c) Shop.Fecon.com, Inc. (http://shop.fecon.com)
 */

namespace Fecon\Sales\Block\Adminhtml\Order\View;

class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
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
