<?php

namespace Fecon\SytelineIntegration\Plugin\Magento\Sales\Model;

/**
 * Plugin to make the Syteline Order ID visible to customers
 */
class Order
{

    /**
     * @var \Magento\Framework\App\State 
     */
    protected $state;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\State $state
     * @return void
     */
    public function __construct (
        \Magento\Framework\App\State $state
    ) {
        $this->state = $state;
    }

    /**
     * Modify increment id result
     *
     * @param \Magento\Sales\Model\Order $subject
     * @param string $result
     * @return string
     */
    public function afterGetIncrementId(\Magento\Sales\Model\Order $subject, $result)
    {
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $areaCode = null;
        }
        if ($subject->getData('syteline_id')) {
            $result = $subject->getData('syteline_id');
        }

        return $result;
    }
}