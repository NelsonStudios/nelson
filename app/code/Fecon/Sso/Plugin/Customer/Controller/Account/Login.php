<?php

namespace Fecon\Sso\Plugin\Customer\Controller\Account;

use Magento\Customer\Model\Url;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Add session messages for regular customers and dealers
 */
class Login
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param EncoderInterface $urlEncoder
     * @param ManagerInterface $messageManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        RequestInterface $request,
        EncoderInterface $urlEncoder,
        ManagerInterface $messageManager,
        UrlInterface $urlBuilder
    ) {
        $this->request = $request;
        $this->urlEncoder = $urlEncoder;
        $this->messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * After execute plugin
     *
     * @param \Magento\Customer\Controller\Account\Login $subject
     * @param \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page $result
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function afterExecute(\Magento\Customer\Controller\Account\Login $subject, $result)
    {
        $referer = $this->request->getParam(Url::REFERER_QUERY_PARAM_NAME);
        $dealersUrl = $this->urlBuilder->getUrl('sso/idp/samlresponse');
        $encodedUrl = $this->urlEncoder->encode($dealersUrl);
        if (strpos($referer, $encodedUrl) === false) {
            $this->messageManager->addNotice(__("Existing FeconConnect user? Use your same credentials."));
        } else {
            $message = "Not an existing FeconConnect user? <a href='" .
                $this->urlBuilder->getUrl('join-our-dealers-program') .
                "'>Click here</a> to learn more about our program.";
            $this->messageManager->addNotice(__($message));
        }

        return $result;
    }
}