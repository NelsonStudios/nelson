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
 * @package   mirasvit/module-search-elastic
 * @version   1.2.75
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchElastic\Adapter\Aggregation;

use Magento\Framework\Search\Dynamic\Algorithm\Repository as AlgorithmRepository;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorageFactory;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

class DynamicBucket
{
    /**
     * @var AlgorithmRepository
     */
    private $algRepo;

    /**
     * @var EntityStorageFactory
     */
    private $entityStorageFactory;

    /**
     * DynamicBucket constructor.
     * @param AlgorithmRepository $algRepo
     * @param EntityStorageFactory $entityStorageFactory
     */
    public function __construct(
        AlgorithmRepository $algRepo,
        EntityStorageFactory $entityStorageFactory
    ) {
        $this->algRepo              = $algRepo;
        $this->entityStorageFactory = $entityStorageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        RequestBucketInterface $bucket,
        array $dimensions,
        array $result,
        DataProviderInterface $provider
    ) {
        /** @var \Magento\Framework\Search\Request\BucketInterface $bucket */
        // $method = $bucket->getName() == 'price_bucket' ? $bucket->getMethod() : 'auto';
        $method = $bucket->getMethod();
        $alg    = $this->algRepo->get($method, ['dataProvider' => $provider]);

        $data = $alg->getItems($bucket, $dimensions, $this->getStorage($result));

        $resultData = $this->prepareData($data);

        return $resultData;
    }

    /**
     * {@inheritdoc}
     */
    private function getStorage(array $queryResult)
    {
        $ids = [];
        foreach ($queryResult['hits']['hits'] as $document) {
            $ids[] = $document['_id'];
        }

        return $this->entityStorageFactory->create($ids);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepareData($data)
    {
        $resultData = [];
        foreach ($data as $v) {
            if ($v['count'] == 0) {
                continue;
            }

            $from = is_numeric($v['from']) ? $v['from'] : '*';
            $to   = is_numeric($v['to']) ? $v['to'] : '*';

            unset($v['from'], $v['to']);

            $rangeName              = "{$from}_{$to}";
            $resultData[$rangeName] = array_merge(['value' => $rangeName], $v);
        }

        return $resultData;
    }
}
