<?php

namespace Fecon\CustomMultishipping\Controller\Checkout;

use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

class AddressesPost extends \Magento\Multishipping\Controller\Checkout
{
    /**
     * Multishipping checkout process posted addresses
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('*/checkout_address/newShipping');
            return;
        }
        $chooseCarrierInfo = $this->getRequest()->getPost('choose_carrier');
        try {
            if ($this->getRequest()->getParam('continue', false)) {
                $this->_getCheckout()->setCollectRatesFlag(true);
                $this->_getState()->setActiveStep(State::STEP_SHIPPING);
                $this->_getState()->setCompleteStep(State::STEP_SELECT_ADDRESSES);
                /* Custom code */
                if(!empty($chooseCarrierInfo)) {
                    $this->_getState()->setSelectedCarriersToSplitStep($chooseCarrierInfo);
                }
                /* End custom code */
                $this->_redirect('*/*/shipping');
            } elseif ($this->getRequest()->getParam('new_address')) {
                $this->_redirect('*/checkout_address/newShipping');
            } else {
                $this->_redirect('*/*/addresses');
            }
            /* Custom code */
            if ($shipToInfo = $this->getRequest()->getPost('ship')) {
                if(!empty($chooseCarrierInfo)) {
                    $this->_getCheckout()->setShippingItemsInformation($shipToInfo, $chooseCarrierInfo);
                } else {
                    $this->_getCheckout()->setShippingItemsInformation($shipToInfo);

                }
            }
            /* End custom code */
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/addresses');
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e->getMessage());
            echo '</pre>';
            exit;
            $this->messageManager->addException($e, __('Address saving problem'));
            $this->_redirect('*/*/addresses');
        }
    }
}
