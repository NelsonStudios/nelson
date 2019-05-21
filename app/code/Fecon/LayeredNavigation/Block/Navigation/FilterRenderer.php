<?php

namespace Fecon\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;
use Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface;

/**
 * Catalog layer filter renderer
 *
 * @api
 * @since 100.0.2
 */
class FilterRenderer extends Template implements FilterRendererInterface
{
    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter)
    {
        $items = $this->orderItemsAlphabeticallyByLabel($filter->getItems());
        $this->assign('filterItems', $items);

        $html = $this->_toHtml();
        $this->assign('filterItems', []);
        return $html;
    }

    /**
     * Sort items alphabetically in ascending order by the label
     *
     * @param array $items
     * @return array
     */
    public function orderItemsAlphabeticallyByLabel($items)
    {
        $aux = [];
        foreach ($items as $key => $item) {
            $aux[$key] = $item->getLabel();
        }
        array_multisort($aux, $items);
        return $items;
    }
}
