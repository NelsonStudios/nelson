<?php

namespace Amasty\Rma\Block\Adminhtml\Settings\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class CustomField extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn('code', ['label' => __('Code'), 'class' => 'required-entry alphanumeric']);
        $this->addColumn('label', ['label' => __('Label'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
