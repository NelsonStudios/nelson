<?php

namespace Serfe\Shipping\Ui\Component\Listing\Column;

use Serfe\Shipping\Api\Data\PreorderInterface;

/**
 * Data source for  Customer
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class Customer extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $customerHelper;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Serfe\Shipping\Helper\CustomerHelper $customerHelper,
        array $components = array(),
        array $data = array()
    ) {
        $this->customerHelper = $customerHelper;
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
                if (isset($item[PreorderInterface::CUSTOMER_ID])) {
                    $customerName = $this->customerHelper->getCustomerName($item[PreorderInterface::CUSTOMER_ID]);
                    $item[$this->getData('name')] = $customerName;
                }
            }
        }

        return $dataSource;
    }
}