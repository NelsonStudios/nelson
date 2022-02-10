<?php
namespace Core\MageplazaSlider\Block;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use Mageplaza\BannerSlider\Helper\Data as bannerHelper;
use Magento\Cms\Model\Template\FilterProvider;

class Widget extends \Mageplaza\BannerSlider\Block\Widget
{

    public $_storeManager;
    protected $_filesystem ;
    protected $_imageFactory;

    public function __construct(
        Template\Context $context,
        bannerHelper $helperData,
        CustomerRepositoryInterface $customerRepository,
        DateTime $dateTime,
        FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        array $data = []
    ) {
        $this->_storeManager=$storeManager;
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        parent::__construct($context,$helperData,$customerRepository,$dateTime,$filterProvider,$data);
    }

    /**
     * @return bool|\Mageplaza\BannerSlider\Model\ResourceModel\Banner\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBannerCollection()
    {
        $sliderId = $this->getData('slider_id');
        if (!$this->helperData->isEnabled() || !$sliderId) {
            return false;
        }

        $sliders = $this->helperData->getActiveSliders();
        foreach ($sliders as $slider) {
            if ($slider->getData('location') != 'custom') {
                continue;
            }

            if ($slider->getId() == $sliderId) {
                $this->setSlider($slider);
                break;
            }
        }
        return parent::getBannerCollection();
    }

    public function getBaseImagePath()
    {
        $imagePath = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $imagePath;
    }
    public function resize($image, $width = null, $height = null)
    {
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('mageplaza/bannerslider/banner/image/').$image;
        if (!file_exists($absolutePath)) return false;
        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('resized/'.$width.'/').$image;
        if (!file_exists($imageResized)) { // Only resize image if not already exists.
            //create image factory...
            $imageResize = $this->_imageFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(TRUE);
            $imageResize->keepTransparency(TRUE);
            $imageResize->keepFrame(FALSE);
            $imageResize->keepAspectRatio(TRUE);
            $imageResize->resize($width,$height);
            //destination folder
            $destination = $imageResized ;
            //save image
            $imageResize->save($destination);
        }
        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'resized/'.$width.'/'.$image;
        return $resizedURL;
  }
}
