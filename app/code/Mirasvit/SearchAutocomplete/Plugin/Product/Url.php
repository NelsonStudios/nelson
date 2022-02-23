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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.2.10
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchAutocomplete\Plugin\Product;

class Url extends \Magento\Framework\Url
{
	/**
     * @param \Magento\Backend\Model\Url $subject
     * @param callable              $proceed
     * @param string|null      $routePath
     * @param array|null      $routeParams
     *
     * @return string
     */
    public function aroundGetUrl($subject, $proceed, $routePath = null, $routeParams = null)
    {
        if (php_sapi_name() == 'cli' && $routePath == 'catalog/product/view') {
            return $this->getUrl($routePath, $routeParams);
        } else {
            return $proceed($routePath, $routeParams);
        }
    }
}
