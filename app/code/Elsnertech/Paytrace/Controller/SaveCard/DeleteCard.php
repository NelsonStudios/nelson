<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Controller\SaveCard;
 
use \Magento\Framework\Controller\ResultFactory;

class DeleteCard extends \Magento\Customer\Controller\AbstractAccount
{
    protected $_pageFactory;
    protected $_request;
    protected $_postFactory;
    protected $_paytraceCollection;
    protected $_helper;
    protected $_paytraceApi;
 
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Elsnertech\Paytrace\Model\Customers $paytraceCollection,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Elsnertech\Paytrace\Model\Api\Api $paytraceApi
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_request = $request;
        $this->_paytraceCollection = $paytraceCollection;
        $this->_messageManager = $messageManager;
        $this->_paytraceApi = $paytraceApi;
        return parent::__construct($context);
    }

    /**
     * @return json
     */
    public function execute()
    {

        $customerId = $this->_request->getParam('customer_id');
        $entityId = $this->_request->getParam('entity_id');
        $customerId = $this->_paytraceApi->decryptText($customerId);
        try {
            $deleteCustomer = $this->_paytraceApi->deleteCustomerProfile(
                $customerId
            );
            if ($deleteCustomer['success'] == true &&
                $deleteCustomer['response_code'] == 162 &&
                $deleteCustomer['customer_id'] == $customerId
            ) {
                $removeCard = $this->_paytraceCollection;
                $remove = $removeCard->setEntityId($entityId);
                $removeSavedCard = $remove->delete();
                $result['message'] = "You successfully removed card.";
                $result['error'] = false ;
            } else {
                $result['error'] = true ;
                $result['message'] = "Something went wrong please try again.";
            }
        } catch (\Exception $e) {
            $result['error'] = true ;
            $result['message'] = __(
                'Error appeared while check paytrace status %1',
                $e->getMessage()
            );
        }

        $resultJson = $this->resultFactory->create(
            ResultFactory::TYPE_JSON
        );
        $resultJson->setData($result);
        return $resultJson;
    }
}
