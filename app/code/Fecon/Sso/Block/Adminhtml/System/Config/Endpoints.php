<?php

namespace Fecon\Sso\Block\Adminhtml\System\Config;

/**
 * Endpoints block
 */
class Endpoints extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected $_endpointRenderer;
    protected $_bindingRenderer;
    protected $_isDefaultRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn(
            'endpoint', ['label' => __('Endpoint'), 'renderer' => $this->_getEndpointRenderer()]
        );
        $this->addColumn(
            'binding', ['label' => __('Binding'), 'renderer' => $this->_getBindingRenderer()]
        );
        $this->addColumn('location', ['label' => __('Location'), 'renderer' => false]);
        $this->addColumn('index', ['label' => __('Index'), 'renderer' => false]);
        $this->addColumn(
            'is_default', ['label' => __('Is Default'), 'renderer' => $this->_getIsDefaultRenderer()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Endpoint');
    }

    /**
     * Retrieve endpoint column renderer
     *
     * @return Endpoint
     */
    protected function _getEndpointRenderer()
    {
        if (!$this->_endpointRenderer) {
            $this->_endpointRenderer = $this->getLayout()->createBlock(
                \Fecon\Sso\Block\Adminhtml\System\Config\Endpoint::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_endpointRenderer;
    }

    /**
     * Retrieve binding column renderer
     *
     * @return Binding
     */
    protected function _getBindingRenderer()
    {
        if (!$this->_bindingRenderer) {
            $this->_bindingRenderer = $this->getLayout()->createBlock(
                \Fecon\Sso\Block\Adminhtml\System\Config\Binding::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->_bindingRenderer;
    }

    /**
     * Retrieve is_default column renderer
     *
     * @return IsDefault
     */
    protected function _getIsDefaultRenderer()
    {
        if (!$this->_isDefaultRenderer) {
            $this->_isDefaultRenderer = $this->getLayout()->createBlock(
                \Fecon\Sso\Block\Adminhtml\System\Config\IsDefault::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_isDefaultRenderer;
    }

    /**
     * Prepare existing row data object.
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $endpointAttribute = $row->getData('endpoint');
        $endpointKey = 'option_' . $this->_getEndpointRenderer()->calcOptionHash($endpointAttribute);
        $options[$endpointKey] = 'selected="selected"';
        $isDefaultAttribute = $row->getData('is_default');
        $isDefaultKey = 'option_' . $this->_getIsDefaultRenderer()->calcOptionHash($isDefaultAttribute);
        $options[$isDefaultKey] = 'selected="selected"';
        $bindingAttribute = $row->getData('binding');
        $bindingKey = 'option_' . $this->_getBindingRenderer()->calcOptionHash($bindingAttribute);
        $options[$bindingKey] = 'selected="selected"';
        $row->setData('option_extra_attrs', $options);
    }
}