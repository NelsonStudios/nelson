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



namespace Mirasvit\Search\Service;

use Magento\Cms\Model\Template\FilterProvider as CmsFilterProvider;
use Magento\Email\Model\TemplateFactory as EmailTemplateFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\App\Emulation as AppEmulation;

class ContentService
{
    /**
     * @var AppEmulation
     */
    private $emulation;

    /**
     * @var CmsFilterProvider
     */
    private $filterProvider;

    /**
     * @var EmailTemplateFactory
     */
    private $templateFactory;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * ContentService constructor.
     * @param AppEmulation $emulation
     * @param CmsFilterProvider $filterProvider
     * @param EmailTemplateFactory $templateFactory
     * @param AppState $appState
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        AppEmulation $emulation,
        CmsFilterProvider $filterProvider,
        EmailTemplateFactory $templateFactory,
        AppState $appState,
        ModuleManager $moduleManager
    ) {
        $this->emulation       = $emulation;
        $this->filterProvider  = $filterProvider;
        $this->templateFactory = $templateFactory;
        $this->appState        = $appState;
        $this->moduleManager   = $moduleManager;
    }

    /**
     * @param int $storeId
     * @param string $html
     * @return mixed|string
     * @throws \Magento\Framework\Exception\MailException
     */
    public function processHtmlContent($storeId, $html)
    {
        $html = $this->cleanHtml($html);
        $this->emulation->stopEnvironmentEmulation();
        $this->emulation->startEnvironmentEmulation($storeId, 'frontend');

        $template = $this->templateFactory->create();
        $template->emulateDesign($storeId);
        $template->setTemplateText($html)
            ->setIsPlain(false);
        $template->setTemplateFilter($this->filterProvider->getPageFilter());
        $html = $template->getProcessedTemplate([]);

        $this->emulation->stopEnvironmentEmulation();

        if ($this->moduleManager->isEnabled('Gene_BlueFoot')) {
            $html = $this->appState->emulateAreaCode(
                'frontend',
                [$this, 'processBlueFoot'],
                [$html]
            );
        }

        return $html;
    }

    /**
     * @param string $html
     * @return string
     */
    private function cleanHtml($html)
    {
        $re = '/(mgz_pagebuilder.*mgz_pagebuilder)*/m';
        preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
            $html = str_replace($match[0], "", $html);
        }
        return $html;
    }

    /**
     * @param string $html
     * @return mixed
     */
    public function processBlueFoot($html)
    {
        $ob          = ObjectManager::getInstance();
        $stageRender = $ob->get('Gene\BlueFoot\Model\Stage\Render');
        $html        = $stageRender->render($html);

        return $html;
    }
}
