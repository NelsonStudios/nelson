<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Block\Form;

class Paytrace extends \Magento\Payment\Block\Form\Cc
{
    /**
     * Paytrace form template
     *
     * @var string
     */
    protected $_template = 'Elsnertech_Paytrace::form/paytrace.phtml';

    public function getVaultEnable()
    {
        
        return $this->getMethod()->getVaultEnable();
    }
}
