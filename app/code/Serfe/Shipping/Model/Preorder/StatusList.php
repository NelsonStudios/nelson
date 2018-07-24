<?php

namespace Serfe\Shipping\Model\Preorder;

use Serfe\Shipping\Ui\Component\Listing\Column\Status;
use Serfe\Shipping\Model\Preorder;

/**
 * Status list
 *
 * @author Xuan Villagran <xuan@serfe.com>
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