<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExport\Model\Import\Order;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

/**
 * Order Data Processor
 */
class DataProcessor
{
    /**
     * Open File Resource
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_varDirectory;
    
    /**
     * File Name
     *
     * @var string
     */
    protected $_fileName;
    
    /**
     * Initialize Processor
	 *
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->_varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }
    
    /**
     * Retrieve File Name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }
    
    /**
     * Set File Name
     *
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName . '.json';
        
        return $this;
    } 
    
    /**
     * Load data from File
     *
     * @param string $identifier     
     * @return $this
     */
    public function load($identifier = null)
    {
        $data = [];
        $filePath = 'import/process/' . $this->_fileName;
        if (!$this->_varDirectory->isReadable($filePath)) {
			return [];
        }
        $content = $this->_varDirectory->readFile($filePath);
        if ($content) {
			$data = json_decode($content, true);
        }
        
        if (null !== $identifier) {
			return isset($data[$identifier]) ? $data[$identifier] : [];
		}
		return is_array($data) ? $data : [];
    }

    /**
     * Save data to File
     *
     * @param array $ids
     * @param string $identifier     
     * @return boolean
     */  
    public function merge(array $ids, $identifier)
    {
        $data = $this->load();
        if (isset($data[$identifier])) {
			$ids = $data[$identifier] + $ids;
        }
        
        $data[$identifier] = $ids;
        $content = json_encode($data);
        
        $dirPath = 'import/process/';
        $filePath = $dirPath . $this->_fileName;
        
        if (!$this->_varDirectory->create($dirPath)) {
            throw new LocalizedException(
                __('Unable to create directory %1.', $dirPath)
            );
        }

        if (!$this->_varDirectory->isWritable($dirPath)) {
            throw new LocalizedException(
                __('Destination folder is not writable or does not exists.')
            );
        }
        $this->_varDirectory->writeFile($filePath, $content);
        return $ids;
    }    
}
 
