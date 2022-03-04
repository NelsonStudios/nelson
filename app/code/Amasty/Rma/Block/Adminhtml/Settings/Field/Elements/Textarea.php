<?php

namespace Amasty\Rma\Block\Adminhtml\Settings\Field\Elements;

class Textarea extends \Magento\Framework\View\Element\AbstractBlock
{
    public function toHtml()
    {
        return '<textarea id="' . $this->getInputId() .
            '"' .
            ' name="' .
            $this->getInputName() .
            '"><%- ' . $this->getColumnName() .' %></textarea>';
    }
}
