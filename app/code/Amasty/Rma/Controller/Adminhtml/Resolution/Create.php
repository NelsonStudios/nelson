<?php

namespace Amasty\Rma\Controller\Adminhtml\Resolution;

use Amasty\Rma\Controller\Adminhtml\AbstractResolution;

class Create extends AbstractResolution
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
