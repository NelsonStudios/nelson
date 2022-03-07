<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Model\Payment;

class Paytracevault
{
    public function __construct(
        \Magento\Vault\Model\VaultPaymentInterface $vault
    ) {
        $this->vault = $vault;
    }
    
    public function mymethod($payment, $amount)
    {
        $this->vault->authorize($payment, $amount);
    }
}
