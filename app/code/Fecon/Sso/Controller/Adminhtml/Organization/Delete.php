<?php


namespace Fecon\Sso\Controller\Adminhtml\Organization;

class Delete extends \Fecon\Sso\Controller\Adminhtml\Organization
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
        $id = $this->getRequest()->getParam('organization_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Fecon\Sso\Model\Organization::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Organization.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['organization_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Organization to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
