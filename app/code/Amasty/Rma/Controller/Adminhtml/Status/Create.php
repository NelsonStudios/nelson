<?php

namespace Amasty\Rma\Controller\Adminhtml\Status;

use Amasty\Rma\Controller\Adminhtml\AbstractStatus;

class Create extends AbstractStatus
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
