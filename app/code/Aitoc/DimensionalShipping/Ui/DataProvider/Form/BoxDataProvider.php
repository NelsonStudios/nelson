<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */

/**
 * Copyright © 2017 Aitoc. All rights reserved.
 */

namespace Aitoc\DimensionalShipping\Ui\DataProvider\Form;

use Aitoc\DimensionalShipping\Model\ResourceModel\Box\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProvider
 *
 * @package Aitoc\DimensionalShipping\Model\Box
 */
class BoxDataProvider extends AbstractDataProvider
{
    /**
     * @var \Aitoc\DimensionalShipping\Model\ResourceModel\Box\Collection
     */
    public $collection;

    /**
     * @var array
     */
    public $loadedData;

    protected $pool;

    private $dataPersistor;

    /**
     * @var array
     */

    /**
     * @param string            $name
     * @param string            $primaryFieldName
     * @param string            $requestFieldName
     * @param CollectionFactory $boxCollectionFactory
     * @param array             $meta
     * @param array             $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $boxCollectionFactory,
        PoolInterface $pool,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection    = $boxCollectionFactory->create();
        $this->pool          = $pool;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }


    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $supplierItem) {
            $data                                     = $supplierItem->getData();
            $this->loadedData[$supplierItem->getId()] = $data;
        }
        $data = $this->dataPersistor->get('aitoc_dimensionalshipping_boxes');
        if (!empty($data)) {
            $supplier = $this->collection->getNewEmptyItem();
            $supplier->setData($data);
            $this->loadedData[$supplier->getId()] = $supplier->getData();
            $this->dataPersistor->clear('aitoc_dimensionalshipping_boxes');
        }

        return $this->loadedData;
    }


    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    public function getFieldsMetaInfo($fieldSetName)
    {
        return parent::getFieldsMetaInfo($fieldSetName); // TODO: Change the autogenerated stub
    }
}
