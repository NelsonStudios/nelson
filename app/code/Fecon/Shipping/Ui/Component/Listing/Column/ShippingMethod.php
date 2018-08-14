<?php

namespace Fecon\Shipping\Ui\Component\Listing\Column;

/**
 * Data source for Shipping Method in backend grid
 *
 * 
 */
class ShippingMethod extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * Carrier
     *
     * @var \Fecon\Shipping\Model\Carrier\ManualShipping 
     */
    protected $manualShipping;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Fecon\Shipping\Model\Carrier\ManualShipping $manualShipping
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Fecon\Shipping\Model\Carrier\ManualShipping $manualShipping,
        array $components = array(),
        array $data = array()
    ) {
        $this->manualShipping = $manualShipping;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Get shipping code
     *
     * @param string $shippingMethod
     * @return string
     */
    protected function getShippingCode($shippingMethod)
    {
        $underscorePos = strpos($shippingMethod, '_');
        $shippingCode = '';
        if ($underscorePos !== false) {
            $underscorePos++;
            $shippingCode = substr($shippingMethod, $underscorePos);
        }

        return $shippingCode;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $shippingMethod = $item[\Fecon\Shipping\Api\Data\PreorderInterface::SHIPPING_METHOD];
                $shippingCode = $this->getShippingCode($shippingMethod);
                $shippingTitle = $this->manualShipping->getCode('method', $shippingCode);
                $item[$this->getData('name')] = $shippingTitle;
            }
        }

        return $dataSource;
    }
}