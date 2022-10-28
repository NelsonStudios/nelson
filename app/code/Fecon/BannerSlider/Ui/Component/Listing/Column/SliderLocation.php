<?php

namespace Fecon\BannerSlider\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Fecon\BannerSlider\Model\Config\Source\Location;

/**
 * Class CommentContent
 * @package Fecon\Blog\Ui\Component\Listing\Columns
 */
class SliderLocation extends \Mageplaza\BannerSlider\Ui\Component\Listing\Column\SliderLocation
{
    /**
     * @param $data
     *
     * @return array
     */
    public function getLocation($data)
    {
        $location = [];
        $data = explode(',', $data);
        foreach ($data as $item) {
            switch ($item) {
                case Location::ALLPAGE_CONTENT_TOP:
                    $location['type'][] = __('All Page');
                    break;
                case Location::ALLPAGE_CONTENT_BOTTOM:
                    $location['type'][] = __('All Page');
                    break;
                case Location::ALLPAGE_PAGE_TOP:
                    $location['type'][] = __('All Page');
                    break;
                case Location::ALLPAGE_PAGE_BOTTOM:
                    $location['type'][] = __('All Page');
                    break;
                case Location::HOMEPAGE_CONTENT_TOP:
                    $location['type'][] = __('Home Page');
                    break;
                case Location::HOMEPAGE_CONTENT_BOTTOM:
                    $location['type'][] = __('Home Page');
                    break;
                case Location::HOMEPAGE_PAGE_TOP:
                    $location['type'][] = __('Home Page');
                    break;
                case Location::HOMEPAGE_PAGE_BOTTOM:
                    $location['type'][] = __('Home Page');
                    break;
                case Location::CATEGORY_CONTENT_TOP:
                    $location['type'][] = __('Category Page');
                    break;
                case Location::CATEGORY_CONTENT_BOTTOM:
                    $location['type'][] = __('Category Page');
                    break;
                case Location::CATEGORY_PAGE_TOP:
                    $location['type'][] = __('Category Page');
                    break;
                case Location::CATEGORY_PAGE_BOTTOM:
                    $location['type'][] = __('Category Page');
                    break;
                case Location::CATEGORY_SIDEBAR_TOP:
                    $location['type'][] = __('Category Page');
                    break;
                case Location::CATEGORY_SIDEBAR_BOTTOM:
                    $location['type'][] = __('Category Page');
                    break;
                case Location::PRODUCT_CONTENT_TOP:
                    $location['type'][] = __('Product Page');
                    break;
                case Location::PRODUCT_CONTENT_BOTTOM:
                    $location['type'][] = __('Product Page');
                    break;
                case Location::PRODUCT_PAGE_TOP:
                    $location['type'][] = __('Product Page');
                    break;
                case Location::PRODUCT_PAGE_BOTTOM:
                    $location['type'][] = __('Product Page');
                    break;
                case Location::USING_SNIPPET_CODE:
                    $location['type'][] = __('Custom');
                    break;
                case Location::CATALOGSEARCH_RESULT_PAGE_TOP:
                    $location['type'][] = __('Search Page');
                    break;
            }
        }

        return $location;
    }
}
