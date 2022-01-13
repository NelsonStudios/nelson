<?php


namespace Fecon\Shipping\Controller\Adminhtml\Preorder;

use Fecon\Shipping\Api\Data\PreorderInterface;

class InlineEdit extends \Magento\Backend\App\Action
{

    protected $jsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        
        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $modelid) {
                    /** @var \Fecon\Shipping\Model\Preorder $model */
                    $model = $this->_objectManager->create(
                        \Fecon\Shipping\Model\Preorder::class
                    )->load($modelid);
                    $status = (int) $model->getStatus();
                    if ($status !== PreorderInterface::STATUS_NEW && $status !== PreorderInterface::STATUS_PENDING) {
                        $messages[] = "You cannot edit the shipping price of a Preorder that has a Canceled or Complete status.";
                        $error = true;
                    } else {
                        try {
                            $model->setData(array_merge($model->getData(), $postItems[$modelid]));
                            $model->save();
                        } catch (\Exception $e) {
                            $messages[] = "[Preorder ID: {$modelid}]  {$e->getMessage()}";
                            $error = true;
                        }
                    }
                }
            }
        }
        
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
