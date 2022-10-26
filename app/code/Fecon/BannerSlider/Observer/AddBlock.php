<?php

namespace Fecon\BannerSlider\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Layout;
use Mageplaza\BannerSlider\Block\Slider;
use Mageplaza\BannerSlider\Helper\Data;
use Mageplaza\BannerSlider\Model\Config\Source\Location;

/**
 * Class AddBlock
 * @package Fecon\AutoRelated\Observer
 */
class AddBlock extends \Mageplaza\BannerSlider\Observer\AddBlock
{
    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        $type = array_search($observer->getEvent()->getElementName(), [
            'header' => 'header',
            'content' => 'content',
            'page-top' => 'columns.top',
            'footer-container' => 'footer-container',
            'sidebar' => 'catalog.leftnav'
        ], true);

        if ($type !== false) {
            /** @var Layout $layout */
            $layout = $observer->getEvent()->getLayout();
            $fullActionName = $this->request->getFullActionName();
            $output = $observer->getTransport()->getOutput();
            foreach ($this->helperData->getActiveSliders() as $slider) {
                $locations = array_filter(explode(',', $slider->getLocation()));
                foreach ($locations as $value) {
                    if ($value === Location::USING_SNIPPET_CODE || $value === 'custom') {
                        continue;
                    }
                    if ($fullActionName === 'catalog_category_view' && $slider->getCategories()) {
                        $categoryId = $this->request->getParam('id');
                        if ($slider->getCategories() !== $categoryId) {
                            continue;
                        }
                    }
                    if ($fullActionName === 'catalog_product_view' && $slider->getProducts()) {
                        $productId = $this->request->getParam('id');
                        if ($slider->getProducts() !== $productId) {
                            continue;
                        }
                    }
                    [$pageType, $location] = explode('.', $value);
                    if (($fullActionName === $pageType || $pageType === 'allpage') &&
                        strpos($location, $type) !== false
                    ) {
                        $content = $layout->createBlock(Slider::class)
                            ->setSlider($slider)
                            ->toHtml();

                        if (strpos($location, 'top') !== false) {
                            if ($type === 'sidebar') {
                                $output = "<div class=\"mp-banner-sidebar\" id=\"mageplaza-bannerslider-block-before-{$type}-{$slider->getId()}\">
                                        $content</div>" . $output;
                            } else {
                                $output = "<div class=\"fecon-bannerslider-block-custom\" id=\"mageplaza-bannerslider-block-before-{$type}-{$slider->getId()}\">
                                        $content</div>" . $output;
                            }
                        } else {
                            if ($type === 'sidebar') {
                                $output .= "<div class=\"mp-banner-sidebar\" id=\"mageplaza-bannerslider-block-after-{$type}-{$slider->getId()}\">
                                        $content</div>";
                            } else {
                                $output .= "<div id=\"mageplaza-bannerslider-block-after-{$type}-{$slider->getId()}\">
                                        $content</div>";
                            }
                        }
                    }
                }
            }

            $observer->getTransport()->setOutput($output);
        }

        return $this;
    }
}
