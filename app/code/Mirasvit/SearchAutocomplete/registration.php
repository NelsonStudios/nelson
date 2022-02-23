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


/** mp comment start */
if (isset($_SERVER) && is_array($_SERVER) && isset($_SERVER['REQUEST_URI'])) {
    if (strpos($_SERVER['REQUEST_URI'], 'searchautocomplete/ajax/typeahead') !== false) {
        require_once 'typeahead.php';
    }
    if (strpos($_SERVER['REQUEST_URI'], 'search/ajax/suggest') !== false) {
        return \Zend_Json::encode([]);
    }
}
/** mp comment end */

$registration = dirname(dirname(dirname(__DIR__)))
    . '/vendor/mirasvit/module-search-autocomplete/src/SearchAutocomplete/registration.php';
if (file_exists($registration)) {
    return;
}

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Mirasvit_SearchAutocomplete',
    __DIR__
);
