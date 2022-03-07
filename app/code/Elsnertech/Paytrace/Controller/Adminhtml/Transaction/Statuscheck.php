<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Controller\Adminhtml\Transaction;

use Magento\Framework\Controller\ResultFactory;

class Statuscheck extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Elsnertech\Paytrace\Model\Api\Api $api
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_api = $api;
    }

    public function execute()
    {
        try {
            $transactionId = $this->getRequest()->getParam('transaction_id');
            $order_id = $this->getRequest()->getParam('order_id');
            $result = $this->_api->getStatusByTransecion(
                $transactionId
            );
            if ($transactionId && isset($result['success'])) {
                if (isset($result['success'])) {
                    $resultPage = $this->resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->prepend(
                        __('Paytrace Status')
                    );
                    $block = $resultPage->getLayout()->getBlock(
                        'elsnertech.paytrace.status'
                    );
                    $block->setData('api_request', $result);
                    $block->setData('order_id', $order_id);
                    return $resultPage;
                } else {
                    $this->messageManager->addError(
                        __('Error appeared while check paytrace status')
                    );
                    $resultRedirect = $this->resultFactory->create(
                        ResultFactory::TYPE_REDIRECT
                    );
                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirect;
                }
            } else {
                $this->messageManager->addError(__('Token not found.'));
                $resultRedirect = $this->resultFactory->create(
                    ResultFactory::TYPE_REDIRECT
                );
                $resultRedirect->setUrl(
                    $this->_redirect->getRefererUrl()
                );
                return $resultRedirect;
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Error appeared while check paytrace status ').$e->getMessage()
            );
            $resultRedirect = $this->resultFactory->create(
                ResultFactory::TYPE_REDIRECT
            );
            $resultRedirect->setUrl(
                $this->_redirect->getRefererUrl()
            );
            return $resultRedirect;
        }
    }
    
    public function _isAllowed()
    {
        return true;
    }
}
