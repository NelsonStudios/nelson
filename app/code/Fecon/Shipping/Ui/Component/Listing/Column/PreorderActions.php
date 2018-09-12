<?php


namespace Fecon\Shipping\Ui\Component\Listing\Column;

class PreorderActions extends \Magento\Ui\Component\Listing\Columns\Column
{

    const URL_PATH_EDIT = 'fecon_shipping/preorder/edit';
    const URL_PATH_DELETE = 'fecon_shipping/preorder/delete';
    const URL_PATH_DETAILS = 'fecon_shipping/preorder/details';
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
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
                if (isset($item['preorder_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'preorder_id' => $item['preorder_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
//                        'delete' => [
//                            'href' => $this->urlBuilder->getUrl(
//                                static::URL_PATH_DELETE,
//                                [
//                                    'preorder_id' => $item['preorder_id']
//                                ]
//                            ),
//                            'label' => __('Delete'),
//                            'confirm' => [
//                                'title' => __('Delete "${ $.$data.title }"'),
//                                'message' => __('Are you sure you wan\'t to delete a "${ $.$data.title }" record?')
//                            ]
//                        ]
                    ];
                }
            }
        }
        
        return $dataSource;
    }
}
