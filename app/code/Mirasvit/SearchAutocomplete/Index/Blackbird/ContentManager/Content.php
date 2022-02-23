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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.2.10
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchAutocomplete\Index\Blackbird\ContentManager;

use Mirasvit\SearchAutocomplete\Index\AbstractIndex;
use Magento\Framework\App\ObjectManager;

class Content extends AbstractIndex
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function __construct()
    {
        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        /** @var \Magefan\Blog\Model\Post $post */
        foreach ($this->getCollection() as $content) {
            $items[] = [
                'name'      => $content->getTitle(),
                'url'       => $content->getLinkUrl(),
            ];
        }

        return $items;
    }
}
