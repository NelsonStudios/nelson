<?php
/**
 * Customer attribute delete controller
 */

namespace IWD\Opc\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('IWD_Opc::attribute_delete');
    }
    
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_objectManager->create(
                    \Magento\Customer\Model\Attribute::class
                );
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The attribute has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addError(__('We can\'t find a attribute to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}