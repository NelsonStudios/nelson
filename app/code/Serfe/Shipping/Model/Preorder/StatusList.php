<?php

namespace Fecon\Shipping\Model\Preorder;

use Fecon\Shipping\Ui\Component\Listing\Column\Status;
use Fecon\Shipping\Model\Preorder;

/**
 * Status list
 *
 * 
 */
class StatusList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * toOptionArray
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => __('-- Please Select a Status --'), 'value' => ''];
        $options[] = ['label' => Status::AVAILABLE, 'value' => Preorder::AVAILABLE];
        $options[] = ['label' => Status::NOT_AVAILABLE, 'value' => Preorder::NOT_AVAILABLE];

        return $options;
    }
}