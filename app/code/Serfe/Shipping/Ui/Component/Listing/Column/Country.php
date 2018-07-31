<?php

namespace Serfe\Shipping\Ui\Component\Listing\Column;

use Serfe\Shipping\Api\Data\PreorderInterface;

/**
 * Data source for Country
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class Country extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Address Helper
     *
     * @var \Serfe\Shipping\Helper\AddressHelper 
     */
    protected $addressHelper;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Serfe\Shipping\Helper\AddressHelper $addressHelper,
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
                    $country = $this->addressHelper->getAddressCountry($item[PreorderInterface::ADDRESS_ID]);
                    $item[$this->getData('name')] = $country;
                }
            }
        }

        return $dataSource;
    }
}