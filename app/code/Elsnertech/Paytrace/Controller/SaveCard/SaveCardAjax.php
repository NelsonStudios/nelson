<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Controller\SaveCard;

use Magento\Framework\Controller\ResultFactory;

class SaveCardAjax extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Elsnertech\Paytrace\Model\Paytracevault $paytraceVault,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_paytraceVault = $paytraceVault;
        $this->_httpContext = $httpContext;
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        return parent::__construct($context);
    }

    /**
     * @return json
     */
    public function execute()
    {
        $resultPage = $this->_pageFactory->create();
        $saveCard = $this->getSaveCard();
        $result['html'] = $this->createHtml($saveCard);
        $result['saveCard'] = $saveCard;
        $resultJson = $this->resultFactory->create(
            ResultFactory::TYPE_JSON
        );
        $resultJson->setData($result);
        return $resultJson;
    }

    /**
     * @return string
     */
    public function createHtml($savedCards)
    {
        $params = ['_secure' => $this->request->isSecure()];
        
        $html = '';
        if (!empty($savedCards)) {
            foreach ($savedCards as $savedCard) {
                $html .= '<div class="row payment-card">
                          <div class="card-inner">
                          <div class="col card-image">';
                          $imagePath = $savedCard['card_image'];

                $html .='<img src="'.$this->assetRepo->getUrlWithParams(
                    'Magento_Payment::images/cc/'.$imagePath,
                    $params
                ).
                '" width="46" height="30" class="payment-icon">';
                $html .= '</div>';
                $html .='<div class="col card-number">
                            '.$savedCard['last4'].'
                            </div>';
                $html .='<div class="col action-delete">
                        <form class="form" id="remove-card-'.
                        $savedCard['entity_id'].
                        '" action="" method="post">
                    <input name="customer_id" value="'.
                    $savedCard['paytrace_customer_id'].
                    '" type="hidden"/>
                    <input name="entity_id" value="'.
                    $savedCard['entity_id'].
                    '" type="hidden"/>
                    <button type="button" class="action delete" 
                    onclick="getRemoveAction(this)">
                        <span>'.__("Remove").'</span>
                    </button>
                </form>
            </div></div></div>';
            }
        } else {
            $html .='<div class="row">
                        <div class="col not-found">
                            '. __("You don't have any cards yet, please add card details.").'
                        </div>
                    </div>';
        }
        return $html;
    }

    public function isLoggedIn()
    {
        return $this->_httpContext->getValue(
            \Magento\Customer\Model\Context::CONTEXT_AUTH
        );
    }

    /**
     * @return boolean|string
     */
    public function getSaveCard()
    {
        if ($this->isLoggedIn()) {
            $saveCard = $this->_paytraceVault->getSavedCards();
            if ($saveCard) {
                return $saveCard;
            } else {
                return false;
            }
        }
    }
}
