<?php
namespace Fecon\SytelineIntegration\Block\Adminhtml\Form\Field\MethodMapping;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Fecon\SytelineIntegration\Block\Adminhtml\Form\Field\MethodMapping\CustomColumn;

/**
 * Class Ranges
 */
class Options extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('method_name', ['label' => __('Name')]);
        $this->addColumn('method_mapping', ['label' => __('ShipVia Code')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $mappingOption = $row->getMethodMapping();
        if ($mappingOption !== null) {
            $options['option_' . $this->getMethodMappingRenderer()->calcOptionHash($mappingOption)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws LocalizedException
     */
    private function getMethodMappingRenderer()
    {
        $this->methodMappingRenderer = $this->getLayout()->createBlock(
            CustomColumn::class,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );
        return $this->methodMappingRenderer;
    }
}
