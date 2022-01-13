<?php


namespace Fecon\Shipping\Controller\Adminhtml\Preorder;

use Magento\Framework\Exception\LocalizedException;
use Fecon\Shipping\Api\Data\PreorderInterface;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->serializer = $serializer;
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
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $data['shipping_method'] = $this->serializer->serialize($data['data']['shipping_method']);
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
                $this->messageManager->addErrorMessage(__('You cannot edit the shipping price of a Preorder that has a Canceled or Complete status.'));
                return $resultRedirect->setPath('*/*/');
            }
        
            $model->setData($data);
        
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Preorder.'));
                $this->dataPersistor->clear('fecon_shipping_preorder');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['preorder_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Preorder.'));
            }
        
            $this->dataPersistor->set('fecon_shipping_preorder', $data);
            return $resultRedirect->setPath('*/*/edit', ['preorder_id' => $this->getRequest()->getParam('preorder_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
