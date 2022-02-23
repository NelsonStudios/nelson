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
 * @package   mirasvit/module-search
 * @version   1.0.156
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


// @codingStandardsIgnoreStart
$magentoElasticsearch = '/vendor/magento/module-elasticsearch/registration.php';
$pathFromAppCode = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . $magentoElasticsearch;
$pathFromVendor = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . $magentoElasticsearch;
$esNativeInstalled = file_exists($pathFromAppCode) || file_exists($pathFromVendor);
if (!$esNativeInstalled) {
    return;
}

$registration = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/vendor/mirasvit/module-search/src/SearchElasticNative/registration.php';
$registrationMarketplace = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/vendor/mirasvit/module-search-elastic-native/registration.php';

if (file_exists($registration) || file_exists($registrationMarketplace)) {
    # module was already installed via composer
    return;
}

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Mirasvit_SearchElasticNative',
    __DIR__
);
// @codingStandardsIgnoreEnd
