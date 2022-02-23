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


namespace Mirasvit\Search\Controller\Adminhtml\Stopword;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Search\Controller\Adminhtml\Stopword;

class Import extends Stopword
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->initPage($resultPage)
            ->getConfig()->getTitle()->prepend(__('Import Stopwords'));

        return $resultPage;
    }
}
