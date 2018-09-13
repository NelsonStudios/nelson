<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Import Job Processor.
 * Validate & import jobs launched by cron or by cli command
 */
abstract class AbstractProcessor
{
    /**
     * @var ObjectManagerFactory
     */
    protected $objectManagerFactory;

    /**
     * @var DecoderInterface $jsonDecoder
     */
    protected $jsonDecoder;

    /**
     * @var Import
     */
    protected $importModel;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var RequestInterface
     */
    protected $request;

    protected $job;

    /**
     * AbstractProcessor constructor.
     *
     * @param DecoderInterface $jsonDecoder
     * @param StoreManagerInterface $storeManager
     * @param StoreResolverInterface $storeResolver
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timezone
     * @param UrlInterface $backendUrl
     */
    public function __construct(
        DecoderInterface $jsonDecoder,
        StoreManagerInterface $storeManager,
        StoreResolverInterface $storeResolver,
        RequestInterface $request,
        LoggerInterface $logger,
        TimezoneInterface $timezone,
        UrlInterface $backendUrl
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->storeManager = $storeManager;
        $this->storeResolver = $storeResolver;
        $this->request = $request;
        $this->backendUrl = $backendUrl;
    }

    /**
     * @param $jobId
     *
     * @return mixed
     */
    abstract protected function getJobModel($jobId);

    /**
     * @return mixed
     */
    abstract protected function getProcessModel();

    /**
     * @return mixed
     */
    abstract protected function getDataMerge();

    /**
     * @return mixed
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param $model
     *
     * @return $this
     */
    public function setJob($model)
    {
        $this->job = $model;

        return $this;
    }

    /**
     * @param int $jobId
     *
     * @return array
     */
    public function prepareJob($jobId)
    {
        $this->setJob($this->getJobModel($jobId));
        $data = [];
        if ($this->getJob()->getId()) {
            $data = $this->getDataMerge();
        }

        return $data;
    }

    /**
     * @param $jobId
     *
     * @return bool|string
     */
    public function process($jobId)
    {
        $data = $this->prepareJob($jobId);
        $result = true;
        try {
            $result = $this->run($data);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }

        return $result;
    }

    abstract public function run($data);
}
