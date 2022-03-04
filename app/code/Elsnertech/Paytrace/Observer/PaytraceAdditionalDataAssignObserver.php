<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Observer;

use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class PaytraceAdditionalDataAssignObserver extends AbstractDataAssignObserver
{
    const MY_FIELD_NAME_INDEX = 'is_saved';

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(
            PaymentInterface::KEY_ADDITIONAL_DATA
        );
        if (!is_array($additionalData) ||
            !isset($additionalData[self::MY_FIELD_NAME_INDEX])
        ) {
            return; // or throw exception depending on your logic
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);
        $paymentInfo->setAdditionalInformation(
            self::MY_FIELD_NAME_INDEX,
            $additionalData[self::MY_FIELD_NAME_INDEX]
        );
    }
}
