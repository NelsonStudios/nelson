<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Export;

use Magento\ImportExport\Model\Import;
use \Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory as StatusCollectionFactory;
use Symfony\Component\Console\Output\ConsoleOutput;

class Order extends \Magento\ImportExport\Model\Export\Entity\AbstractEntity
{
    use \Firebear\ImportExport\Traits\General;

    const ORDERS = 'orders';

    const ITEM = 'item';

    const SEPARATOR = '|';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $factory;

    protected $entityCollectionFactory;

    protected $entityCollection;

    protected $itemsPerPage = null;

    protected $headerColumns = [];

    /**
     * @var \Firebear\ImportExport\Model\Source\Factory
     */
    protected $createFactory;

    protected $children;

    /**
     * @var \Firebear\ImportExport\Helper\Data
     */
    protected $helper;

    /**
     * @var ConsoleOutput
     */
    protected $output;

    protected $_debugMode;

    protected $joined = true;

    protected $counts;

    protected $main;

    protected $addFields = [];

    /**
     * Json Format Column Names
     *
     * @var array
     */
    protected $jsonField = [
        'product_options',
        'additional_information'
    ];

    /**
     * Blob Format Column Names
     *
     * @var array
     */
    protected $blobField = [
        'shipping_label'
    ];

    /**
     * Ignore Column Names
     *
     * @var array
     */
    protected $ignoreField = [
        'parent_item'
    ];

    /**
     * No Escape Column Names
     *
     * @var array
     */
    protected $noEscapeField = [
        'invoices_comment',
        'shipments_comment',
        'creditmemos_comment',
        'payments_transaction',
        'creditmemos_item',
        'invoices_item',
        'shipments_item',
        'shipments_track',
        'taxs_item'
    ];

    /**
     * Order Status Collection
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection
     */
    protected $statusCollection;

    /**
     * Order Statuses Label
     *
     * @var array
     */
    protected $_status;

    /**
     * Order constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Firebear\ImportExport\Model\Source\Factory $createFactory
     * @param StatusCollectionFactory $statusCollectionFactory
     * @param Dependencies\Config $configDi
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Firebear\ImportExport\Model\Source\Factory $createFactory,
        \Firebear\ImportExport\Model\Export\Dependencies\Config $configDi,
        \Firebear\ImportExport\Helper\Data $helper,
        StatusCollectionFactory $statusCollectionFactory,
        ConsoleOutput $output
    ) {
        $this->_logger = $logger;
        $this->createFactory = $createFactory;
        $this->children = $configDi->get();
        $this->helper = $helper;
        $this->output = $output;
        $this->_debugMode = $helper->getDebugMode();
        $this->statusCollection = $statusCollectionFactory->create();

        parent::__construct(
            $localeDate,
            $config,
            $resource,
            $storeManager
        );
    }

    /**
     * Retrieve Shipment Ids Map
     *
     * @param string $status
     * @return string
     */
    protected function _getStatusLabel($status)
    {
        if (null === $this->_status) {
            $this->_status = [];
            foreach ($this->statusCollection as $item) {
                $this->_status[$item->getStatus()] = $item->getLabel();
            }
        }
        return isset($this->_status[$status])
            ? $this->_status[$status]
            : '';
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'order';
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        return __('Orders');
    }

    /**
     * @return mixed
     */
    public function _getHeaderColumns()
    {
        return $this->customHeadersMapping($this->headerColumns);
    }

    /**
     * @param bool $resetCollection
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected function _getEntityCollection($resetCollection = false)
    {
        if ($resetCollection || empty($this->entityCollection)) {
            $this->entityCollection = $this->entityCollectionFactory->create();
        }

        return $this->entityCollection;
    }

    /**
     * @param $model
     * @return mixed
     */
    protected function _getEntityCollectionSecond($model)
    {

        return $model->create();
    }

    public function export()
    {
        //Execution time may be very long
        set_time_limit(0);
        if (!isset($this->_parameters['behavior_data']['deps'])) {
            $this->addLogWriteln(__('You have not selected items'), $this->output);
            return false;
        }
        $this->counts = 0;
        $deps = $this->_parameters['behavior_data']['deps'];
        $collections = [];
        $this->addLogWriteln(__('Begin Export'), $this->output);
        $this->addLogWriteln(__('Scope Data'), $this->output);

        foreach ($this->children as $typeName => $type) {
            if ($typeName == 'catalog_category' || !isset($type['fields'])) {
                continue;
            }
            foreach ($type['fields'] as $name => $values) {
                if (in_array($name, $deps)) {
                    if ($this->main == null) {
                        $this->main = $name;
                    }
                    if ($name != $this->main) {
                        $this->addFields[] = $name;
                        if (in_array($values['parent'] . "_" . $name, $this->addFields)) {
                            $searchKey = array_search($name, $this->addFields);
                            unset($this->addFields[$searchKey]);
                        }
                    }
                    $model = $this->createFactory->create($values['collection']);
                    $object = [
                        'model' => $model,
                        'main_field' => $values['main_field'],
                        'parent' => $values['parent'],
                        'parent_field' => $values['parent_field'],
                        'children' => [],
                        'delete' => []
                    ];
                    if (isset($values['fields']) && $name != $this->main) {
                        foreach ($values['fields'] as $fieldKey => $fieldValue) {
                            if (isset($fieldValue['delete']) && $fieldValue['delete'] == 1) {
                                $this->addFields[] = $name . "_" . $fieldKey;
                                $object['delete'][] = $fieldKey;
                            }
                        }
                    }
                    if ($values['parent'] && in_array($values['parent'], $deps)) {
                        $this->searchChildren($values['parent'], $name, $collections, $object);
                    } else {
                        $collections[$name] = $object;
                    }
                }
            }
        }

        if ($this->_parameters['behavior_data']['file_format'] == 'xml') {
            $this->joined = false;
        }

        $writer = $this->getWriter();
        foreach ($collections as $key => $collection) {
            $this->runCollection($key, $collection, $writer);
        }

        return [$writer->getContents(), $this->counts];
    }

    /**
     * @param $parent
     * @param $name
     * @param $collections
     * @param $object
     * @return bool
     */
    protected function searchChildren($parent, $name, &$collections, $object)
    {
        if (isset($collections[$parent])) {
            $collections[$parent]['children'][$name] = $object;
            return true;
        }
        foreach ($collections as &$child) {
            $this->searchChildren($parent, $name, $child['children'], $object);
        }
    }

    /**
     * @param $key
     * @param $collection
     * @param $writer
     */
    public function runCollection($key, $collection, $writer)
    {
        $page = 0;
        $this->entityCollectionFactory = $collection['model'];

        while (true) {
            ++$page;
            $entityCollection = $this->_getEntityCollection(true);
            $entityCollection = $this->prepareEntityCollection($entityCollection, $key);
            $this->paginateCollection($page, $this->getItemsPerPage());
            if ($entityCollection->getSize() == 0) {
                break;
            }
            $exportData = $this->getExportData(
                $key,
                isset($collection['children']) ? $collection['children'] : [],
                isset($collection['main_field']) ? $collection['main_field'] : '',
                $entityCollection,
                1
            );
            if ($page == 1) {
                $writer->setHeaderCols($this->_getHeaderColumns());
            }
            $this->addLogWriteln(__('Write to Source'), $this->output);
            foreach ($exportData as $dataRow) {
                $writer->writeRow($this->customFieldsMapping($dataRow));
                $this->counts++;
            }
            if ($entityCollection->getCurPage() >= $entityCollection->getLastPageNumber()) {
                break;
            }
        }
    }

    /**
     * @param $key
     * @param $collection
     * @return string
     */
    public function runCollectionSecond($key, $collection, $parents, $level)
    {

        $text = [];
        $page = 0;
        $array[self::ITEM] = [];
        while (true) {
            ++$page;
            $entityCollection = $this->_getEntityCollectionSecond($collection['model']);
            $entityCollection = $this->prepareEntityCollection($entityCollection, $key);
            if (!empty($parents)) {
                foreach ($parents as $field => $value) {
                    $entityCollection->addFieldToFilter($field, $value);
                }
            }
            $this->paginateCollection($page, $this->getItemsPerPage());
            if ($entityCollection->getSize() == 0) {
                break;
            }
            $exportData = $this->getExportData(
                $key,
                isset($collection['children']) ? $collection['children'] : [],
                isset($collection['main_field']) ? $collection['main_field'] : '',
                $entityCollection,
                ++$level
            );

            foreach ($exportData as $kk => $dataRow) {
                $getData = $this->getRow($dataRow, $level);
                if ($this->joined) {
                    $text[] = $getData;
                } else {
                    $array[] = [self::ITEM => $getData];
                }
            }
            if ($entityCollection->getCurPage() >= $entityCollection->getLastPageNumber()) {
                break;
            }
        }
        if (!$this->joined) {
            $text = $array;
        }

        return $text;
    }

    /**
     * @return array
     */
    protected function getExportData($key, $children, $mainField, $collection, $level)
    {
        $behaviors = $this->_parameters['behavior_data'];
        $exportData = [];
        try {
            $rawData = $this->collectRawData($key, $collection);

            foreach ($rawData as $mainKey => $dataRow) {
                if ($dataRow) {
                    if (isset($dataRow['store_name'])) {
                        $dataRow['store_name'] = str_replace("\n", " ", $dataRow['store_name']);
                    }
                    if ($key == 'orders') {
                        $dataRow['status_label'] = isset($dataRow['status'])
                            ? $this->_getStatusLabel($dataRow['status'])
                            : '';
                    }
                    if (!empty($children)) {
                        foreach ($children as $keySecond => $collectSecond) {
                            $parents = [];
                            if (isset($collectSecond['parent'])) {
                                $code = $this->getChangeCode($key);
                                if ($collectSecond['parent'] == $key) {
                                    $parents[$collectSecond['parent_field']] = $dataRow[$code];
                                }
                            }
                            $dataRow[$keySecond] = $this->runCollectionSecond(
                                $keySecond,
                                $collectSecond,
                                $parents,
                                $level
                            );

                        }
                    }
                    $insert = 0;
                    if ($this->joined) {
                        $inRecord = 0;
                        $deps = array_keys($children);
                        foreach ($children as $keySecond => $collectSecond) {
                            if (sizeof($dataRow[$keySecond]) > 0) {
                                $anotherFields = array_diff($deps, [$keySecond]);
                                $insert++;
                                $tempData = $dataRow;
                                $newData = $dataRow;

                                foreach ($anotherFields as $field) {
                                    $tempData[$field] = "";
                                }
                                $temp = $dataRow[$keySecond];

                                $newData = $this->emptyArray($newData);
                                if (isset($dataRow[$mainField])) {
                                    $newData[$mainField] = $dataRow[$mainField];
                                }
                                foreach ($temp as $keyLast => $itemLast) {
                                    if (isset($collectSecond['delete']) && sizeof($collectSecond['delete']) > 0) {
                                        foreach ($collectSecond['delete'] as $fieldDelete) {
                                            if (empty($itemLast[$fieldDelete])) {
                                                continue;
                                            }
                                            $tempDelete = $itemLast[$fieldDelete];
                                            $tempData[$keySecond . "_" . $fieldDelete] = $tempDelete;
                                            $newData[$keySecond . "_" . $fieldDelete] = $tempDelete;
                                            $anotherFields[] = $keySecond . "_" . $fieldDelete;
                                            unset($itemLast[$fieldDelete]);
                                        }
                                    }
                                    $val = implode($behaviors['multiple_value_separator'],
                                        $this->changeData($itemLast));
                                    if (!$inRecord) {
                                        $tempData[$keySecond] = $val;
                                        $exportData[] = $this->checkColumns($tempData, $key);
                                        $inRecord++;
                                    } else {
                                        $newData[$keySecond] = $val;
                                        $exportData[] = $this->checkColumns($newData, $key);
                                    }
                                }
                            }
                        }
                    }
                    if (!$insert) {

                        $exportData[] = $this->checkColumns($dataRow, $key);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->addLogWriteln($e->getMessage(), $this->output, 'error');
            $this->_logger->critical($e);
        }

        return $exportData;
    }

    /**
     * @return array
     */
    public function getAttributeCollection()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getFieldsForExport()
    {
        $options = [];
        foreach ($this->children as $typeName => $type) {
            if ($typeName == 'order') {
                foreach ($type['fields'] as $name => $values) {
                    $model = $this->createFactory->create($values['model']);
                    $options[$name] = [
                        'label' => __($values['label']),
                        'optgroup-name' => $name,
                        'value' => []
                    ];
                    $fields = $this->getChildHeaders($model);
                    foreach ($fields as $field) {
                        $options[$name]['value'][] = [
                            'label' => $field,
                            'value' => $field
                        ];
                    }
                }
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getFieldsForFilter()
    {
        $options = [];
        foreach ($this->children as $typeName => $type) {
            if ($typeName == 'order') {
                foreach ($type['fields'] as $name => $values) {
                    $model = $this->createFactory->create($values['model']);
                    $fields = $this->getChildHeaders($model);
                    $mergeFields = [];
                    if (isset($values['fields'])) {
                        $mergeFields = $values['fields'];
                    }
                    foreach ($fields as $field) {
                        if (isset($mergeFields[$field]) && $mergeFields[$field]['delete']) {
                            continue;
                        }
                        $options[$name][] = [
                            'label' => $field,
                            'value' => $field
                        ];
                    }
                }
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getFieldColumns()
    {
        $options = [];
        foreach ($this->children as $typeName => $type) {
            if ($typeName == 'order') {
                foreach ($type['fields'] as $name => $values) {
                    $mergeFields = [];
                    if (isset($values['fields'])) {
                        $mergeFields = $values['fields'];
                    }
                    $model = $this->createFactory->create($values['model']);
                    $fields = $this->describeTable($model);
                    foreach ($fields as $key => $field) {
                        $type = $this->helper->convertTypesTables($field['DATA_TYPE']);
                        $select = [];
                        if (isset($mergeFields[$key])) {
                            if (!$mergeFields[$key]['delete']) {
                                $type = $mergeFields[$key]['type'];
                                $select = $mergeFields[$key]['options'];
                            }
                        }
                        $options[$name][] = ['field' => $key, 'type' => $type, 'select' => $select];
                    }
                }
            }
        }

        return $options;
    }

    protected function getTableColumns()
    {
        $options = [];
        foreach ($this->children as $typeName => $type) {
            if ($typeName == 'catalog_category' || !isset($type['fields']) || !isset($type['fields'])) {
                continue;
            }
            foreach ($type['fields'] as $name => $values) {
                $model = $this->createFactory->create($values['model']);
                $fields = $this->describeTable($model);
                foreach ($fields as $key => $field) {
                    $type = $this->helper->convertTypesTables($field['DATA_TYPE']);
                    $options[$name][$key] = ['type' => $type];
                }
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        return array_keys($this->describeTable());
    }

    /**
     * @param null $model
     * @return array
     */
    protected function describeTable($model = null)
    {

        if ($model) {
            $resource = $model->getResource();
        } else {
            $resource = $this->factory->create()->getResource();
        }
        $table = $resource->getMainTable();
        $fields = $resource->getConnection()->describeTable($table);

        return $fields;
    }

    /**
     * @return array
     */
    protected function collectRawData($key, $collection)
    {
        $instr = $this->scopeFields($key);
        $allFields = $this->_parameters['all_fields'];
        $data = [];

        foreach ($collection as $itemId => $item) {
            $temp = null;
            if (!$allFields) {
                $temp = $this->changedColumns($item->getData(), $instr);
            } else {
                $temp = $this->addPartColumns($item, $instr, $key);
            }
            if ($key == $this->main) {
                foreach ($this->addFields as $field) {
                    $temp[$field] = '';
                }
            }
            $data[] = $temp;
        }

        $collection->clear();

        return $data;
    }

    /**
     * @param $key
     * @return mixed|string
     */
    protected function getChangeCode($key)
    {
        $newCode = '';
        foreach ($this->children as $typeName => $type) {
            if ($typeName == 'catalog_category' || !isset($type['fields'])) {
                continue;
            }
            foreach ($type['fields'] as $name => $values) {
                if ($name == $key) {
                    $newCode = $values['main_field'];
                }
            }
        }
        $instr = $this->scopeFields($key);

        $keyCode = $this->getKeyFromList($instr['list'], $newCode);
        if ($keyCode !== false && isset($instr['replaces'][$keyCode])) {
            $newCode = $instr['replaces'][$keyCode];
        }

        return $newCode;
    }

    /**
     * @param $key
     * @return array
     */
    protected function scopeFields($key)
    {
        $deps = $this->_parameters['dependencies'];
        $numbers = [];
        foreach ($deps as $ki => $dep) {
            if ($dep == $key) {
                $numbers[] = $ki;
            }
        }

        $listCodes = $this->filterCodes($numbers, $this->_parameters['list']);
        $replaces = $this->filterCodes($numbers, $this->_parameters['replace_code']);
        $replacesValues = $this->filterCodes($numbers, $this->_parameters['replace_value']);
        $instr = [
            'list' => $listCodes,
            'replaces' => $replaces,
            'replacesValues' => $replacesValues
        ];

        return $instr;
    }

    /**
     * @param $list
     * @param $search
     * @return false|int|string
     */
    protected function getKeyFromList($list, $search)
    {
        return array_search($search, $list);
    }

    /**
     * @param $numbers
     * @param $list
     * @return array
     */
    protected function filterCodes($numbers, $list)
    {
        $array = [];

        foreach ($list as $ki => $item) {
            if (in_array($ki, $numbers)) {
                $array[$ki] = $item;
            }
        }

        return $array;
    }

    /**
     * @param $data
     * @return array
     */
    protected function changedColumns($data, $instr)
    {
        $newData = [];
        foreach ($data as $code => $value) {
            if (in_array($code, $instr['list'])) {
                $ki = $this->getKeyFromList($instr['list'], $code);
                $newCode = $code;
                if ($ki !== false && isset($instr['replaces'][$ki])) {
                    $newCode = $instr['replaces'][$ki];
                }
                $newData[$newCode] = $value;
                if ($ki !== false && isset($instr['replacesValues'][$ki])
                    && !empty($instr['replacesValues'][$ki])) {
                    $newData[$newCode] = $instr['replacesValues'][$ki];
                }
            } else {
                $newData[$code] = $value;
            }
        }

        return $newData;
    }

    /**
     * @param $item
     * @return array
     */
    protected function addPartColumns($item, $instr, $key)
    {
        $newData = [];
        $reqCode = "";
        foreach ($this->children as $typeName => $type) {
            if ($typeName == 'catalog_category' || !isset($type['fields'])) {
                continue;
            }
            foreach ($type['fields'] as $name => $values) {
                if ($name == $key) {
                    $reqCode = $values['main_field'];
                }
            }
        }
        if (!in_array($reqCode, $instr['list'])) {
            $newData[$reqCode] = $item->getData($reqCode);
        }

        foreach ($instr['list'] as $k => $code) {
            $newCode = $code;
            if (isset($instr['replaces'][$k])) {
                $newCode = $instr['replaces'][$k];
            }
            $newData[$newCode] = $item->getData($code);

            if (isset($instr['replacesValues'][$k])
                && !empty($instr['replacesValues'][$k])) {
                $newData[$newCode] = $instr['replacesValues'][$k];
            }
        }

        return $newData;
    }

    /**
     * @param $data
     * @param $key
     * @return mixed
     */
    protected function checkColumns($data, $key)
    {
        $deps = $this->_parameters['dependencies'];
        $instr = $this->scopeFields($key);
        $allFields = $this->_parameters['all_fields'];
        if ($allFields) {
            foreach ($data as $code => $value) {
                if (!in_array($code, $instr['replaces']) && !in_array($code, $deps)) {
                    unset($data[$code]);
                }
            }
        }

        return $data;
    }

    /**
     * @param $rowData
     * @return mixed
     */
    protected function customHeadersMapping($rowData)
    {
        foreach ($rowData as $key => $fieldName) {
            if (isset($this->_fieldsMap[$fieldName])) {
                $rowData[$key] = $this->_fieldsMap[$fieldName];
            }
        }

        return $rowData;
    }

    /**
     * @param $page
     * @param $pageSize
     */
    protected function paginateCollection($page, $pageSize)
    {
        $this->_getEntityCollection()->setPage($page, $pageSize);
    }

    /**
     * @return int|null
     */
    protected function getItemsPerPage()
    {
        if ($this->itemsPerPage === null) {
            $memoryLimit = trim(ini_get('memory_limit'));
            $lastMemoryLimitLetter = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
            switch ($lastMemoryLimitLetter) {
                case 'g':
                    $memoryLimit *= 1024;
                // fall-through intentional
                case 'm':
                    $memoryLimit *= 1024;
                // fall-through intentional
                case 'k':
                    $memoryLimit *= 1024;
                    break;
                default:
                    // minimum memory required by Magento
                    $memoryLimit = 250000000;
            }

            // Tested one product to have up to such size
            $memoryPerProduct = 100000;
            // Decrease memory limit to have supply
            $memoryUsagePercent = 0.8;
            // Minimum Products limit
            $minProductsLimit = 500;
            // Maximal Products limit
            $maxProductsLimit = 5000;

            $this->itemsPerPage = (int)
                ($memoryLimit * $memoryUsagePercent - memory_get_usage(true)) / $memoryPerProduct;
            if ($this->itemsPerPage < $minProductsLimit) {
                $this->itemsPerPage = $minProductsLimit;
            }
            if ($this->itemsPerPage > $maxProductsLimit) {
                $this->itemsPerPage = $maxProductsLimit;
            }
        }

        return $this->itemsPerPage;
    }

    /**
     * @param $rowData
     * @return mixed
     */
    protected function customFieldsMapping($rowData)
    {
        if ($this->joined) {
            foreach ($rowData as $key => $value) {
                if (is_array($value)) {
                    $rowData[$key] = $this->optionRowToCellString($value);
                }
            }
        }

        return $rowData;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->children as $key => $child) {
            $options[] = ['label' => $child['name'], 'value' => $key];
        }

        return $options;
    }

    /**
     * @param $model
     * @return array
     */
    public function getChildHeaders($model)
    {
        return array_keys($this->describeTable($model));
    }

    /**
     * @param array $data
     * @return string
     */
    protected function getColumns(array $data)
    {
        $behaviors = $this->_parameters['behavior_data'];
        $columns = [];
        if ($data) {
            foreach ($data as $key => $item) {
                $columns[$key] = false;
            }
        }

        return implode($behaviors['multiple_value_separator'], array_keys($columns));
    }

    /**
     * @param array $data
     * @param array $level
     * @return string
     */
    public function getRow(array $data, $level)
    {
        $rows = [];
        if ($this->joined) {
            foreach ($data as $key => $value) {
                /* json format */
                if (is_array($value) && in_array($key, $this->jsonField)) {
                    $json = json_encode($value);
                    $rows[$key] = $level > 2 ? rawurlencode($json) : $json;
                } elseif ($value && in_array($key, $this->blobField)) { /* binary format */
                    $rows[$key] = rawurlencode(base64_encode($value));
                } elseif (is_array($value)) { /* base array */
                    $rows[$key] = $this->optionRowToCellString($value);
                } elseif (is_object($value) && in_array($key, $this->ignoreField)) { /* disable children object */
                    continue;
                } elseif (is_object($value)) { /* base object */
                    $rows[$key] = $this->optionRowToCellString($value->getData());
                } elseif ($value && $level > 1 && !in_array($key, $this->noEscapeField)) { /* no escape */
                    $rows[$key] = rawurlencode($value);
                } else { /* other */
                    if (!$value) {
                        $value = "";
                    }
                    $rows[$key] = $value;
                }
            }
        } else {
            foreach ($data as $key => $value) {
                /* json format */
                if (is_array($value) && in_array($key, $this->jsonField)) {
                    $rows[$key] = json_encode($value);
                } elseif ($value && in_array($key, $this->blobField)) { /* binary format */
                    $rows[$key] = base64_encode($value);
                } elseif (is_array($value)) {  /* base array */
                    $rows[$key] = $value;
                } elseif (is_object($value) && in_array($key, $this->ignoreField)) { /* disable children object */
                    continue;
                } elseif (is_object($value)) { /* base object */
                    $rows[$key] = $value->getData();
                } else { /* other */
                    if (!$value) {
                        $value = "";
                    }
                    $rows[$key] = $value;
                }
            }
        }
        return $rows;
    }

    /**
     * @param $option
     * @return string
     */
    protected function optionRowToCellString($option)
    {
        $result = [];

        foreach ($option as $key => $value) {
            if (!is_array($value)) {
                $value = rawurlencode($value);
                $result[] = $key . ImportProduct::PAIR_NAME_VALUE_SEPARATOR . $value;
            } else {
                $result[] = $key . "=[" . $this->optionRowToCellString($value) . "]";
            }
        }

        return implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $result);
    }

    /**
     * @param $collection
     * @param $entity
     * @return mixed
     */
    protected function prepareEntityCollection($collection, $entity)
    {
        if (!isset($this->_parameters[\Firebear\ImportExport\Model\ExportJob\Processor::EXPORT_FILTER_TABLE])
            || !is_array($this->_parameters[\Firebear\ImportExport\Model\ExportJob\Processor::EXPORT_FILTER_TABLE])) {
            $exportFilter = [];
        } else {
            $exportFilter = $this->_parameters[\Firebear\ImportExport\Model\ExportJob\Processor::EXPORT_FILTER_TABLE];
        }
        $filters = [];
        foreach ($exportFilter as $data) {
            if ($data['entity'] == $entity) {
                $filters[$data['field']] = $data['value'];
            }
        }

        $fields = $this->getTableColumns();
        foreach ($filters as $key => $value) {
            if (isset($fields[$entity][$key])) {
                $type = $fields[$entity][$key]['type'];
                if ('text' == $type) {
                    $value = $value;
                    if (is_scalar($value)) {
                        trim($value);
                    }
                    $collection->addFieldToFilter($key, ['like' => "%{$value}%"]);
                } elseif ('int' == $type) {
                    if (is_array($value) && count($value) == 2) {
                        $from = array_shift($value);
                        $to = array_shift($value);

                        if (is_numeric($from)) {
                            $collection->addFieldToFilter($key, ['from' => $from]);
                        }
                        if (is_numeric($to)) {
                            $collection->addFieldToFilter($key, ['to' => $to]);
                        }
                    }
                } elseif ('date' == $type) {
                    if (is_array($value) && count($value) == 2) {
                        $from = array_shift($exportFilter[$value]);
                        $to = array_shift($exportFilter[$value]);

                        if (is_scalar($from) && !empty($from)) {
                            $date = (new \DateTime($from))->format('m/d/Y');
                            $collection->addFieldToFilter($key, ['from' => $date, 'date' => true]);
                        }
                        if (is_scalar($to) && !empty($to)) {
                            $date = (new \DateTime($to))->format('m/d/Y');
                            $collection->addFieldToFilter($key, ['to' => $date, 'date' => true]);
                        }
                    }
                }
            }
        }

        return $collection;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function emptyArray($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = "";
        }

        return $data;
    }

    /**
     * @param $data
     * @return array
     */
    public function changeData($data)
    {

        $str = [];
        foreach ($data as $key => $value) {
            $str[] = $key . "=" . $value;
        }

        return $str;

    }
}
