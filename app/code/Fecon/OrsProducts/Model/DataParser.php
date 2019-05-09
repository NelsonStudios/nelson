<?php

namespace Fecon\OrsProducts\Model;

/**
 * Class to parse data from CSV file
 */
class DataParser implements \Fecon\OrsProducts\Api\DataParserInterface
{

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * Constructor
     *
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Filesystem\DirectoryList $directoryList
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
    }

    /**
     * {@inheritdoc}
     */
    public function readCsv($fileName)
    {
        $magentoRoot = $this->directoryList->getRoot();
        $fileFullPath = $magentoRoot . self::CSV_DIRECTORY . $fileName;
        try {
            $importProductRawData = $this->csvProcessor->getData($fileFullPath);
        } catch (\Exception $ex) {
            $importProductRawData = false;
        }

        return $importProductRawData;
    }
}