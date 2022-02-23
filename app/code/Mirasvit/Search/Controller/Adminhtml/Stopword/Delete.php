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

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Mirasvit\Search\Api\Data\StopwordInterface;
use Mirasvit\Search\Api\Repository\StopwordRepositoryInterface;
use Mirasvit\Search\Api\Service\StopwordServiceInterface;
use Mirasvit\Search\Controller\Adminhtml\Stopword;

class Delete extends Stopword
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * Delete constructor.
     * @param Filter $filter
     * @param StopwordRepositoryInterface $stopwordRepository
     * @param StopwordServiceInterface $stopwordService
     * @param Context $context
     */
    public function __construct(
        Filter $filter,
        StopwordRepositoryInterface $stopwordRepository,
        StopwordServiceInterface $stopwordService,
        Context $context
    ) {
        $this->filter = $filter;

        parent::__construct($stopwordRepository, $stopwordService, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $ids = [];

        if ($this->getRequest()->getParam(StopwordInterface::ID)) {
            $ids = [$this->getRequest()->getParam(StopwordInterface::ID)];
        }

        if ($this->getRequest()->getParam(Filter::SELECTED_PARAM)
            || $this->getRequest()->getParam(Filter::EXCLUDED_PARAM)
        ) {
            $ids = $this->filter->getCollection($this->stopwordRepository->getCollection())->getAllIds();
        }

        if ($ids) {
            foreach ($ids as $id) {
                try {
                    $page = $this->stopwordRepository->get($id);
                    $this->stopwordRepository->delete($page);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }

            $this->messageManager->addSuccessMessage(
                __('%1 item(s) was removed', count($ids))
            );
        } else {
            $this->messageManager->addErrorMessage(__('Please select item(s)'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
