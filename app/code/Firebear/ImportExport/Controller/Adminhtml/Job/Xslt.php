<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Controller\Adminhtml\Job;

use Firebear\ImportExport\Controller\Adminhtml\Job as JobController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory;
use Firebear\ImportExport\Model\JobFactory;
use Firebear\ImportExport\Api\JobRepositoryInterface;
use Firebear\ImportExport\Model\Job\Processor;
use Magento\Framework\Registry;
use Firebear\ImportExport\Model\Import\Platforms;
use Firebear\ImportExport\Ui\Component\Listing\Column\Entity\Import\Options;
use Firebear\ImportExport\Helper\Data;

class Xslt extends JobController
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Framework\FilesystemFactory
     */
    protected $fileSystem;

    protected $file;

    protected $modelOutput;

    /**
     * Mapvalidate constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param JobFactory $jobFactory
     * @param JobRepositoryInterface $repository
     * @param JsonFactory $jsonFactory
     * @param DirectoryList $directoryList
     * @param Platforms $platforms
     * @param Data $helper
     * @param Options $options
     * @param \Magento\Framework\FilesystemFactory $filesystemFactory
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        JobFactory $jobFactory,
        JobRepositoryInterface $repository,
        JsonFactory $jsonFactory,
        DirectoryList $directoryList,
        Data $helper,
        \Magento\Framework\FilesystemFactory $filesystemFactory,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Firebear\ImportExport\Model\Output\Xslt $modelOutput
    ) {
        parent::__construct($context, $coreRegistry, $jobFactory, $repository);
        $this->jsonFactory = $jsonFactory;
        $this->directoryList = $directoryList;
        $this->helper = $helper;
        $this->jsonDecoder = $jsonDecoder;
        $this->fileSystem = $filesystemFactory;
        $this->file = $file;
        $this->modelOutput = $modelOutput;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $messages = [];
        if ($this->getRequest()->isAjax()) {
            //read required fields from xml file
            $formData = $this->getRequest()->getParam('form_data');
            $importData = [];

            foreach ($formData as $data) {
                $exData = explode('+', $data);
                $index = $exData[0];
                $importData[$index] = $exData[1];
            }
            $directory = $this->fileSystem->create()->getDirectoryWrite(DirectoryList::ROOT);
            $file = $directory->getAbsolutePath() ."/" . $importData['file_path'];
            $dest = $this->file->read($file);
            $messages = [];
            try {
                $result = $this->modelOutput->convert($dest, $importData['xslt']);
                return $resultJson->setData(
                    [
                        'result' => $result
                    ]
                );
            } catch (\Exception $e) {
                $messages[] = $e->getMessage();
            }

            return $resultJson->setData(
                [
                    'error' => $messages
                ]
            );
        }
    }
}
