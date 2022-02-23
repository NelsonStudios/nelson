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
 * @package   mirasvit/module-search-elastic
 * @version   1.2.75
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchElastic\Model\Indexer\Scope;

use Mirasvit\Core\Service\CompatibilityService;

class StateMediator
{
    const USE_TEMPORARY_INDEX = 'use_temporary_table';
    const USE_REGULAR_INDEX = 'use_main_table';

    /**
     * @var null
     */
    private $state = null;

    public function __construct()
    {
        if (class_exists('Magento\CatalogSearch\Model\Indexer\Scope\State')) {
            $this->state = CompatibilityService::getObjectManager()
                ->get('Magento\CatalogSearch\Model\Indexer\Scope\State');
        } else {
            $this->state = null;
        }
    }

    /**
     * @return bool|null
     */
    public function get()
    {
        if (empty($this->state)) {
            return false;
        } else {
            return $this->state;
        }
    }
}
