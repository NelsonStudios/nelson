<?php

namespace Fecon\SytelineIntegration\Model\Config\Source;

/**
 * Source Model for SOAP version configuration
 *
 * 
 */
class SoapVersion implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * toOptionArray
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'SOAP_1_1', 'label' => __('SOAP_1_1')],['value' => 'SOAP_1_2', 'label' => __('SOAP_1_2')]];
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return ['SOAP_1_1' => __('SOAP_1_1'),'SOAP_1_2' => __('SOAP_1_2')];
    }
}
