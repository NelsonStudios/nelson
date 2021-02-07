<?php

namespace Fecon\Shipping\Ui\Component\Listing\Column;

use Fecon\Shipping\Ui\Component\Create\Form\Shipping\Options;

/**
 * Data source for Shipping Method in backend grid
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
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Fecon\Shipping\Model\Carrier\ManualShipping $manualShipping
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Fecon\Shipping\Model\Carrier\ManualShipping $manualShipping,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $components = array(),
        array $data = array()
    ) {
        $this->manualShipping = $manualShipping;
        $this->serializer = $serializer;
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

    protected function getShippingMethods($shippingMethod)
    {
        $shippingArray = $this->serializer->unserialize($shippingMethod);
        $shippingTitles = [];
        foreach ($shippingArray as $shipping) {
            if (isset(Options::SHIPPING_METHODS[$shipping])) {
                $shippingTitle = Options::SHIPPING_METHODS[$shipping];
                $shippingTitles[] = $shippingTitle;
            }
        }

        return implode(',', $shippingTitles);
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
                //$shippingTitle = $this->manualShipping->getCode('method', $shippingCode);
                //$item[$this->getData('name')] = $this->getShippingMethods($shippingMethod);
                $item[$this->getData('name')] = $shippingMethod;
            }
        }

        return $dataSource;
    }
}
