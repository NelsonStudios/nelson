<?php

namespace Serfe\SytelineIntegration\Ui\Component\Listing\Column;

use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;

/**
 * Status Class
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class Status extends Column
{
    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [], array $data = []
    ) {
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
                
                $status = $this->getItemStatus($item);

                $item[$this->getData('name')] = $status;
            }
        }

        return $dataSource;
    }
    
    /**
     * Get Item Status
     *
     * @param array $item
     * @return string
     */
    protected function getItemStatus($item)
    {
        $status = (int) $item["success"];

        switch ($status) {
            case 0:
                $enabled = "Failed";
                break;
            case 1;
                $enabled = "Successful";
                break;
            default:
                $enabled = "Failed";
                break;

        }
        
        return $enabled;
    }
}