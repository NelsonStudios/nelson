<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.156
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Block\Adminhtml\Config\Form\Field;

class LongTailExpressions extends AbstractFieldArray
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->addColumn('match_expr', ['label' => __('Match Expression')]);
        $this->addColumn('replace_expr', ['label' => __('Replace Expression')]);
        $this->addColumn('replace_char', ['label' => __('Replace Char')]);

        parent::_construct();
    }
}
