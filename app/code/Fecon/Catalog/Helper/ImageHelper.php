<?php

namespace Fecon\Catalog\Helper;

/**
 * Helper to resize custom images
 */
class ImageHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Filesystem 
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Image\AdapterFactory 
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface 
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Resize a Category Image
     *
     * @param string $image
     * @param int $width
     * @param int|null $height
     * @param boolean $keepAspectRatio
     * @param boolean $constrainOnly
     * @param boolean $keepFrame
     * @return string
     */
    public function resizeCategoryImage(
        $image,
        $width,
        $height = null,
        $keepAspectRatio = false,
        $constrainOnly = true,
        $keepFrame = true
    ) {
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/category/') . $image;

        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('resized/' . $width . '/') . $image;
        //create image factory...
        $imageResize = $this->_imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly($constrainOnly);
        $imageResize->keepTransparency(TRUE);
        $imageResize->keepFrame($keepFrame);
        $imageResize->keepAspectRatio($keepAspectRatio);
        $imageResize->resize($width, $height);
        //destination folder                
        $destination = $imageResized;
        //save image      
        $imageResize->save($destination);

        $resizedURL = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'resized/' . $width . '/' . $image;
        return $resizedURL;
    }
}