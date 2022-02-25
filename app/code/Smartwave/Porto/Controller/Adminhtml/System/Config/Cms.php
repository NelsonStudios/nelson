<?php

namespace Smartwave\Porto\Controller\Adminhtml\System\Config;

abstract class Cms extends \Magento\Backend\App\Action {
    protected function _import()
    {
        return $this->_objectManager->get(
            \Smartwave\Porto\Model\Import\Cms::class
        )->importCms($this->getRequest()->getParam('import_type'), $this->getRequest()->getParam('demo_version'));
    }
}
