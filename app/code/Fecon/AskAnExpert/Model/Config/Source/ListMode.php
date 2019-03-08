<?php
namespace Fecon\AskAnExpert\Model\Config\Source;

class ListMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('Left')],
            ['value' => '1', 'label' => __('Right')],
           
        ];
    }
}
