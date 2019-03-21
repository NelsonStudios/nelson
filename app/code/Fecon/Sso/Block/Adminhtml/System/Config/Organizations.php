<?php

namespace Fecon\Sso\Block\Adminhtml\System\Config;

/**
 * Description of Organizations
 */
class Organizations extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected function _prepareToRender()
    {
        $this->addColumn('organization', ['label' => __('Organization'), 'renderer' => false]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Organization');
    }
}