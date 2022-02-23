<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.156
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Ui\Validator;

use Magento\Framework\Data\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\Search\Model\Config;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Config
     */
    private $config;

    /**
     * DataProvider constructor.
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        Config $config,
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->config     = $config;
        $this->collection = $collectionFactory->create();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return mixed|void|null
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [
            'items' => [
                [
                    'id'            => 0,
                    'id_field_name' => 'id',
                    'limit'         => $this->config->getResultsLimit(),
                    'engine'        => $this->config->getEngine(),
                ],
            ],
        ];

        return $data;
    }
}
