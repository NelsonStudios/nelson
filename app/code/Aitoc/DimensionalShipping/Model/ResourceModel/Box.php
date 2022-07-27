<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


/**
 * Copyright © 2017 Aitoc. All rights reserved.
 */


namespace Aitoc\DimensionalShipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Box extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('aitoc_dimensional_shipping_boxes', 'item_id');
    }
}
