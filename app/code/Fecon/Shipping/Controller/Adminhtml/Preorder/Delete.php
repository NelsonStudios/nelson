<?php


namespace Fecon\Shipping\Controller\Adminhtml\Preorder;

class Delete extends \Fecon\Shipping\Controller\Adminhtml\Preorder
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('preorder_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(
                    \Fecon\Shipping\Model\Preorder::class
                );
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Preorder.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['preorder_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Preorder to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
