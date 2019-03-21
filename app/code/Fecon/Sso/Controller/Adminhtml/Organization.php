<?php


namespace Fecon\Sso\Controller\Adminhtml;

abstract class Organization extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'Fecon_Sso::config_fecon_sso';
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Fecon'), __('Fecon'))
            ->addBreadcrumb(__('Organization'), __('Organization'));
        return $resultPage;
    }
}
