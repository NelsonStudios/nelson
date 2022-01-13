<?php

namespace Fecon\Shipping\Controller\Adminhtml\Preorder;

use Magento\Framework\Exception\LocalizedException;
use Fecon\Shipping\Api\Data\PreorderInterface;

/**
 * Cancel Controller
 */
class Cancel extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('preorder_id');

        $model = $this->_objectManager->create(
            \Fecon\Shipping\Model\Preorder::class
        )->load($id);
        if (!$model->getId() && $id) {
            $this->messageManager->addErrorMessage(__('This Preorder no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }
        $status = (int) $model->getStatus();
        if ($status !== PreorderInterface::STATUS_NEW && $status !== PreorderInterface::STATUS_PENDING) {
            $this->messageManager->addErrorMessage(__('You cannot cancel a Preorder that has a Canceled or Complete status.'));
            return $resultRedirect->setPath('*/*/');
        }

        $model->setStatus(PreorderInterface::STATUS_CANCELED);
        $model->setIsAvailable(0);

        try {
            $model->save();
            $this->messageManager->addSuccessMessage(__('You cancel the Preorder.'));
            $this->dataPersistor->clear('fecon_shipping_preorder');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while cancel the Preorder.'));
        }
        $data = $model->getData();
        $this->dataPersistor->set('fecon_shipping_preorder', $data);
        return $resultRedirect->setPath('*/*/edit', ['preorder_id' => $this->getRequest()->getParam('preorder_id')]);
    }
}