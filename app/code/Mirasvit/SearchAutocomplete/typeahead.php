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


namespace Mirasvit\SearchAutocomplete;

if (php_sapi_name() == "cli") {
    return;
}

$configFile = dirname(dirname(dirname(__DIR__))) . '/etc/typeahead.json';

if (stripos(__DIR__, 'vendor') !== false) {
    $configFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/app/etc/typeahead.json';
}

if (!file_exists($configFile)) {
    return \Zend_Json::encode([]);
}

$config = \Zend_Json::decode(file_get_contents($configFile));

class TypeAheadAutocomplete
{
    /**
     * @var array
     */
    private $config;

    /**
     * TypeAheadAutocomplete constructor.
     * @param array $config
     */
    public function __construct(
        array $config
    ) {
        $this->config = $config;
    }

    /**
     * @return mixed|string
     */
    public function process()
    {
        $query = $this->getQueryText();
        $query = substr($query, 0, 2);
        return isset($this->config[$query])?$this->config[$query]:'';
    }

    /**
     * @return mixed|string
     */
    private function getQueryText()
    {
        return filter_input(INPUT_GET, 'q') !== null ? filter_input(INPUT_GET, 'q') : '';
    }
}

$result = (new TypeAheadAutocomplete($config))->process();

/** mp comment start **/
exit(\Zend_Json::encode($result));
/** mp comment end **/

/** mp uncomment start
return \Zend_Json::encode($result);
mp uncomment end **/
