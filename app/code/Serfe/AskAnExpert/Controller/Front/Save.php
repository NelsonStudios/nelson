<?php
namespace Serfe\AskAnExpert\Controller\Front;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $dataPersistor;
    protected $formKeyValidator;
    //const CP_PAGE_HEADING = 'askanexpert/active_display/contact_heading';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    protected $_transportBuilder;
    /**
     * @var CustomerRepository
     */
    protected $customerRepository;
    protected $_contactModel;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;
    private static $_siteVerifyUrl = "https://www.google.com/recaptcha/api/siteverify?";
    private $_secret;
    private static $_version = "php_1.0";
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CustomerRepository $customerRepository
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomerRepository $customerRepository,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Serfe\AskAnExpert\Helper\Data $myModuleHelper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Serfe\AskAnExpert\Model\ContactFactory $_contactModel
    ) {
        $this->storeManager = $storeManager;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepository = $customerRepository;
        $this->subscriberFactory = $subscriberFactory;
        $this->_mymoduleHelper = $myModuleHelper;
        $this->messageManager = $messageManager;
        $this->_contactModel = $_contactModel;
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context);
    }

    /**
     * Save form data
     *
     * @return void|null
     */
    public function execute()
    {
        $error = false;
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->_redirect('*/*/');
            return;
        }
        try {
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($post);
            if ($this->_mymoduleHelper->isCaptchaEnabled()) {
                $captcha = $this->getRequest()->getParam('g-recaptcha-response');
                $secret = $this->_mymoduleHelper->getsecurekey();
                $response = null;
                $path = self::$_siteVerifyUrl;
                $dataC =  [
                'secret' => $secret,
                'remoteip' => $_SERVER["REMOTE_ADDR"],
                'v' => self::$_version,
                'response' => $captcha
                ];
                $req = "";
                foreach ($dataC as $key => $value) {
                    $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
                }
                // Cut the last '&'
                $req = substr($req, 0, strlen($req)-1);
                $response = file_get_contents($path . $req);
                $answers = json_decode($response, true);
                if (trim($answers['success']) == true) {
                    /**** Start Email Block ****/
                    if ($this->_mymoduleHelper->getreceipt() != '') {
                        $sender = '';
                        if($this->_mymoduleHelper->getemailsender() == '') {
                            $sender = 'test@devphase.io';
                        } else {
                            $sender = $this->_mymoduleHelper->getemailsender();
                        }
                        $transport = $this->_transportBuilder
                        ->setTemplateIdentifier($this->_mymoduleHelper->getemailtemplate())
                        ->setTemplateOptions(
                            [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                            ]
                        )
                        ->setTemplateVars(['data' => $postObject])
                        ->setFrom($sender)
                        ->addTo($this->_mymoduleHelper->getreceipt())
                        ->setReplyTo($post['email'])
                        ->getTransport();
                        $transport->sendMessage();
                    }
                    /**** End Email Block ****/
                    $contactModel = $this->_contactModel->create();
                    $contactModel->setData($post);
                    $contactModel->save();

                    $this->messageManager->addSuccess(__('Your inquiry has been submitted successfully.We will contact you back shortly.'));
            
                    $this->_redirect($this->_redirect->getRefererUrl());
                    return;

                } else {
                    // Dispay Captcha Error
                   $error = true;
                   throw new \Exception();
                }
            } else {
                /**** Start Email Block ****/
                if ($this->_mymoduleHelper->getreceipt() != '') {
                    $sender = '';
                    if($this->_mymoduleHelper->getemailsender() == '') {
                        $sender = 'test@devphase.io';
                    } else {
                        $sender = $this->_mymoduleHelper->getemailsender();
                    }
                    $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($this->_mymoduleHelper->getemailtemplate())
                    ->setTemplateOptions(
                        [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($sender)
                    ->addTo($this->_mymoduleHelper->getreceipt())
                    ->setReplyTo($post['email'])
                    ->getTransport();
                    $transport->sendMessage();
                }
                /**** End Email Block ****/

                $contactModel = $this->_contactModel->create();
                $contactModel->setData($post);
                $contactModel->save();
                
                $this->messageManager->addSuccess(__('Your inquiry has been submitted successfully. We will contact you back shortly.'));
                
                $this->_redirect($this->_redirect->getRefererUrl());
                return;
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('We can\'t process your request right now. Please try again later.')
            );
            $this->getDataPersistor()->set('askanexpert', $post);
            $this->_redirect($this->_redirect->getRefererUrl());
            return;
        }
    }

    /**
     * getDataPersistor
     * 
     * @return \Magento\Framework\App\Request\DataPersistor
     */
    private function getDataPersistor() {
        if ($this->dataPersistor === null) {
            $this->dataPersistor = ObjectManager::getInstance()->get(DataPersistorInterface::class);
        }
        return $this->dataPersistor;
    }
}
