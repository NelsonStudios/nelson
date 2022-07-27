<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Helper;

use Aitoc\DimensionalShipping\Model\BoxRepository;
use Aitoc\DimensionalShipping\Model\Convertor\ConvertorFactory;
use Aitoc\DimensionalShipping\Model\OrderItemBoxFactory;
use Aitoc\DimensionalShipping\Model\ProductOptionsRepository;
use Aitoc\DimensionalShipping\Model\ResourceModel\Box\CollectionFactory as BoxCollectionFactory;
use Aitoc\DimensionalShipping\Model\ResourceModel\OrderBox\CollectionFactory as OrderBoxCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data
{
    const XML_PATH_CONFIG = 'DimensionalShipping/general/';
    const PACK_SEPARATELY_ITEM = 1;
    const PACK_SEPARATELY= 2;

    /**
     * @var array
     */
    private $fields = ['ai_width', 'ai_height', 'ai_length', 'ai_special_box', 'ai_select_box', 'ai_pack_separately'];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var BoxCollectionFactory
     */
    private $boxCollectionFactory;

    /**
     * @var BoxRepository
     */
    private $boxRepository;

    /**
     * @var ProductOptionsRepository
     */
    private $dimensionalProductOptionsRepository;

    /**
     * @var OrderBoxCollectionFactory
     */
    private $orderBoxCollectionFactory;

    /**
     * @var ConvertorFactory
     */
    private $convertorFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        BoxCollectionFactory $boxCollectionFactory,
        BoxRepository $boxRepository,
        ProductOptionsRepository $dimensionalProductOptionsRepository,
        OrderBoxCollectionFactory $orderBoxCollectionFactory,
        ConvertorFactory $convertorFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->boxCollectionFactory = $boxCollectionFactory;
        $this->boxRepository = $boxRepository;
        $this->dimensionalProductOptionsRepository = $dimensionalProductOptionsRepository;
        $this->orderBoxCollectionFactory = $orderBoxCollectionFactory;
        $this->convertorFactory = $convertorFactory;
    }

    /**
     * @return array
     */
    public function getContainerData()
    {
        return [
            'container_ai_length' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'breakLine' => '',
                            'label' => '',
                            'required' => 0,
                            'sortOrder' => 0,
                            'component' => 'Magento_Ui/js/form/components/group',
                            'dataScope' => ''
                        ]
                    ]
                ],
                'children' => [
                    'ai_length' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'int',
                                    'formElement' => 'input',
                                    'visible' => 1,
                                    'required' => 0,
                                    'default' => '',
                                    'label' => 'Product Length',
                                    'code' => 'product_length',
                                    'notice' => 'The length of the product for the carton selection algorithm.',
                                    'source' => '',
                                    'globalScope' => '',
                                    'sortOrder' => 3,
                                    'componentType' => 'field',
                                    'addafter' => $this->getGeneralConfig('unit')
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'container_ai_width' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'breakLine' => '',
                            'label' => '',
                            'required' => 0,
                            'sortOrder' => 1,
                            'component' => 'Magento_Ui/js/form/components/group',
                            'dataScope' => ''
                        ]
                    ]
                ],
                'children' => [
                    'ai_width' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'int',
                                    'formElement' => 'input',
                                    'visible' => 1,
                                    'required' => 0,
                                    'default' => '',
                                    'label' => 'Product Width',
                                    'code' => 'product_width',
                                    'notice' => 'The width of the product for the carton selection algorithm.',
                                    'source' => '',
                                    'globalScope' => '',
                                    'sortOrder' => 0,
                                    'componentType' => 'field',
                                    'addafter' => $this->getGeneralConfig('unit')
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'container_ai_height' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'breakLine' => '',
                            'label' => '',
                            'required' => 0,
                            'sortOrder' => 2,
                            'component' => 'Magento_Ui/js/form/components/group',
                            'dataScope' => ''
                        ]
                    ]
                ],
                'children' => [
                    'ai_height' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'int',
                                    'formElement' => 'input',
                                    'visible' => 1,
                                    'required' => 0,
                                    'default' => '',
                                    'label' => 'Product Height',
                                    'code' => 'product_height',
                                    'notice' => 'The height of the product for the carton selection algorithm.',
                                    'source' => '',
                                    'globalScope' => '',
                                    'sortOrder' => 0,
                                    'componentType' => 'field',
                                    'addafter' => $this->getGeneralConfig('unit')
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'container_ai_special_box' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'breakLine' => '',
                            'label' => '',
                            'required' => 0,
                            'sortOrder' => 4,
                            'component' => 'Magento_Ui/js/form/components/group',
                            'dataScope' => ''
                        ]
                    ]
                ],
                'children' => [
                    'ai_special_box' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'int',
                                    'formElement' => 'select',
                                    'visible' => 1,
                                    'required' => 0,
                                    'default' => '',
                                    'label' => 'Special Box for this product',
                                    'code' => 'special_box',
                                    'source' => '',
                                    'globalScope' => '',
                                    'sortOrder' => 0,
                                    'componentType' => 'field',
                                    'component' => 'Aitoc_DimensionalShipping/js/form/element/options',
                                    'options' => [
                                        ['label' => 'Disable', 'value' => 0],
                                        ['label' => 'Enable', 'value' => 1]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'container_ai_select_box' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'breakLine' => '',
                            'label' => '',
                            'required' => 0,
                            'sortOrder' => 5,
                            'component' => 'Magento_Ui/js/form/components/group',
                            'dataScope' => ''
                        ]
                    ]
                ],
                'children' => [
                    'ai_select_box' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'int',
                                    'formElement' => 'select',
                                    'required' => 0,
                                    'label' => 'Box',
                                    'notice' => 'Selected box should be volumetrically suitable to this product.',
                                    'code' => 'special_box',
                                    'source' => '',
                                    'default' => '',
                                    'visible' => 0,
                                    'globalScope' => '',
                                    'sortOrder' => 0,
                                    'componentType' => 'field',
                                    'options' => [],
                                    'required' => 1,
                                    'validation' => [
                                        'required-entry' => true
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'container_ai_pack_separately' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'breakLine' => '',
                            'label' => '',
                            'required' => 0,
                            'sortOrder' => 6,
                            'component' => 'Magento_Ui/js/form/components/group',
                            'dataScope' => ''
                        ]
                    ]
                ],
                'children' => [
                    'ai_pack_separately' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'int',
                                    'formElement' => 'select',
                                    'visible' => 1,
                                    'required' => 0,
                                    'default' => '',
                                    'label' => 'Special packing rules for this product',
                                    'notice' => 'if special box is selected, "NO" is considered as "Don\'t pack this product with other goods".',
                                    'code' => 'pack_separately',
                                    'source' => '',
                                    'globalScope' => '',
                                    'sortOrder' => 0,
                                    'componentType' => 'field',
                                    'options' => [
                                        ['label' => 'No', 'value' => 0],
                                        ['label' => 'Don\'t pack this product with other goods', 'value' => self::PACK_SEPARATELY],
                                        ['label' => 'Pack each single item into separate box', 'value' => self::PACK_SEPARATELY_ITEM]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @param      $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_CONFIG . $code, $storeId);
    }

    /**
     * @param      $field
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param null $option
     * @param      $orderId
     *
     * @return array|\Magento\Framework\DataObject[]\
     */
    public function getBoxListForOrder($orderId)
    {
        $orderBoxCollection = $this->orderBoxCollectionFactory->create()
            ->addFieldToFilter('order_id', $orderId)->getItems();
        $items = $orderBoxCollection;
        foreach ($orderBoxCollection as $item) {
            $optionsArray[] = ['label' => '-- Select Box --', 'value' => ''];
            foreach ($items as $rowItem) {
                $rowItem->getData();
                $optionsArray[] = ['label' => $rowItem->getName(), 'value' => $rowItem->getItemId()];
            }
        }

        return $items;
    }

    /**
     * @param null $option
     *
     * @return array|\Magento\Framework\DataObject[]
     */
    public function getBoxList($option = null)
    {
        $collection = $this->boxCollectionFactory->create();
        $items = $collection->getItems();
        $optionsArray[] = ['label' => '-- Select Box --', 'value' => ''];
        foreach ($items as $rowItem) {
            $rowItem->getData();
            $optionsArray[] = ['label' => $rowItem->getName(), 'value' => $rowItem->getItemId()];
        }
        if ($option == 'array') {
            foreach ($items as $rowItem) {
                $boxes[] = $rowItem->getData();
            }

            return $boxes;
        }
        if ($option == 'items') {
            return $items;
        }

        return $optionsArray;
    }

    /**
     * @param $boxId
     *
     * @return \Aitoc\DimensionalShipping\Api\Data\BoxInterface|mixed
     */
    public function getBoxById($boxId)
    {
        try {
            return $this->boxRepository->get($boxId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @param $orderItem
     *
     * @return bool
     */
    public function checkProductsType($orderItem)
    {
        return !in_array($orderItem->getProductType(), ['downloadable', 'virtual']);
    }

    /**
     * @param $object
     * @param $type
     *
     * @return mixed
     */
    public function convertUnits($object, $type)
    {
        if ($type == 'box') {
            $boxFields = $this->getBoxModelFields('long');
        } else {
            $boxFields = $this->getProductOptionsModelFields('long');
        }
        foreach ($boxFields as $field) {
            $start = 'get';
            $field = $start . str_replace("_", "", $field);
            $convertor = $this->convertorFactory->create(
                [
                    'value' => $object->{$field}(),
                    'unit' => $this->getGeneralConfig('unit')
                ]
            );
            $result = $convertor->to(['mm']);
            $field = str_replace("get", "set", $field);
            $object->{$field}($result['mm']);
        }

        return $object;

    }

    /**
     * @param $type
     *
     * @return array
     */
    public function getBoxModelFields($type)
    {
        if ($type == 'long') {
            $boxFields = $this->boxRepository->getUnitsConfigFieldsLong();
        } else {
            $boxFields = $this->boxRepository->getUnitsConfigFieldsWeight();
        }

        return $boxFields;
    }

    /**
     * @param $type
     *
     * @return array
     */
    public function getProductOptionsModelFields($type)
    {
        if ($type == 'long') {
            $productOptionsModelFields = $this->dimensionalProductOptionsRepository->getUnitsConfigFieldsLong();
        } else {
            $productOptionsModelFields = $this->dimensionalProductOptionsRepository->getUnitsConfigFieldsWeight();
        }

        return $productOptionsModelFields;
    }
}
