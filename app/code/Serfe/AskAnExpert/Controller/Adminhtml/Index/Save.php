<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Serfe\AskAnExpert\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Serfe\AskAnExpert\Model\Contact;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Inspection\Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;

//use Magento\Framework\Stdlib\DateTime\DateTime;
//use Magento\Ui\Component\MassAction\Filter;
//use Serfe\News\Model\ResourceModel\Test\CollectionFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    protected $scopeConfig;
    protected $_transportBuilder;
    protected $_escaper;
    protected $inlineTranslation;
    protected $_dateFactory;
    //protected $_modelNewsFactory;
  //  protected $collectionFactory;
   //  protected $filter;
    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Serfe\AskAnExpert\Helper\Data $myModuleHelper
    ) {
       // $this->filter = $filter;
       // $this->collectionFactory = $collectionFactory;
        $this->dataPersistor = $dataPersistor;
         $this->_transportBuilder = $transportBuilder;
         $this->scopeConfig = $scopeConfig;
         $this->_escaper = $escaper;
        $this->_dateFactory = $dateFactory;
         $this->_mymoduleHelper = $myModuleHelper;
         $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('contact_id');

            if (isset($data['status']) && $data['status'] === 'true') {
                $data['status'] = Block::STATUS_ENABLED;
            }
            if (empty($data['contact_id'])) {
                $data['contact_id'] = null;
            }

           
            /** @var \Magento\Cms\Model\Block $model */
            $model = $this->_objectManager->create('Serfe\AskAnExpert\Model\Contact')->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This Submission no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            if ($data['reply']!='') {
                $data['status'] = 0;
            }
            //print_r($data);
           // exit;
            date_default_timezone_set($this->_mymoduleHelper->timezone());
            $rep_time = date("Y-m-d").' '.date("h:i:sa");
            $data['reply_time']= $rep_time;
        // print_r($data);
          //  echo $this->_mymoduleHelper->timezone();
         //   echo  $this->_dateFactory->create()->gmtDate();
        //  exit;
            
            $model->setData($data);


            $this->inlineTranslation->suspend();
            try {
                 /////////// custom code end
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($data);

              //  print_r($postObject);
              // echo $this->_mymoduleHelper->getemailreplytemplate();
             //  exit;
              //   print_r($postObject);
              //   exit;
                $transport = $this->_transportBuilder
                ->setTemplateIdentifier($this->_mymoduleHelper->getemailreplytemplate())
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($this->_mymoduleHelper->getemailsender())
                ->addTo($data['email'])
                ->setReplyTo($this->_mymoduleHelper->getemailsender())
                ->getTransport();

                $transport->sendMessage();

                    //////////////////// email
                $model->save();
                $this->messageManager->addSuccess(__('Email sent successfully'));
                $this->dataPersistor->clear('serfe_askanexpert');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['contact_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Submission.'));
            }

            $this->dataPersistor->set('serfe_askanexpert', $data);
            return $resultRedirect->setPath('*/*/edit', ['contact_id' => $this->getRequest()->getParam('contact_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

 /*   private function _processNewsImage($data, $model){
                
        try{
            
        
            $media_dir_obj = $this->_objectManager->get('Magento\Framework\Filesystem')
                                                    ->getDirectoryRead(DirectoryList::MEDIA);                                                                        
            $media_dir = $media_dir_obj->getAbsolutePath();


            if(!empty($_FILES['newsimage']['name'])){

                $Uploader = $this->_objectManager->create(
                                               'Magento\MediaStorage\Model\File\Uploader',
                                                ['fileId' => 'newsimage']);

                $Uploader->setAllowCreateFolders(true);
                $Uploader->setAllowRenameFiles(true);

                $news_dir = $media_dir.'/news/';                                
                $result = $Uploader->save($news_dir);

                unset($result['tmp_name']);
                unset($result['path']);

                $data['newsimage'] = 'news/'.$Uploader->getUploadedFileName();

            }else{

                if(isset($data['newsimage']['delete'])){

                    $data['newsimage'] = '';

                }else{

                    if($model->getId()) { //edit mode

                        if($model->getNewsimage() != ''){                                        
                            $data['newsimage'] = $model->getNewsimage();
                        }

                    }else{
                        $data['newsimage'] = '';
                    }
                }
            }
            
            if(isset($data['newsimage']))
                return $data['newsimage'];    
            
        
        } catch (\Exception $e) {
        
                $this->messageManager->addError(
                        __($e->getMessage())
                );                                
        }            
        
    } */
}
