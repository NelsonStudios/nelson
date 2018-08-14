<?php

namespace Fecon\Shipping\Ui\Component\Listing\Column;

use Fecon\Shipping\Api\Data\PreorderInterface;

/**
 * Data source for ZipCode
 *
 * 
 */
class ZipCode extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * Address Helper
     *
     * @var \Fecon\Shipping\Helper\AddressHelper 
     */
    protected $addressHelper;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Fecon\Shipping\Helper\AddressHelper $addressHelper,
        array $components = array(),
        array $data = array()
    ) {
        $this->addressHelper = $addressHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
                if (isset($item[PreorderInterface::ADDRESS_ID])) {
                    $zipCode = $this->addressHelper->getAddressPostcode($item[PreorderInterface::ADDRESS_ID]);
                    $item[$this->getData('name')] = $zipCode;
                }
            }
        }

        return $dataSource;
    }
}