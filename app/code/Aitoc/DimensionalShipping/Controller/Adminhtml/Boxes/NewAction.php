<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Controller\Adminhtml\Boxes;

class NewAction extends \Aitoc\DimensionalShipping\Controller\Adminhtml\Boxes
{
    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
