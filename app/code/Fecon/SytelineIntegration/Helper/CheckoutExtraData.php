<?php

namespace Fecon\SytelineIntegration\Helper;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @author xuanv
 */
class CheckoutExtraData
{

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
    * @var SerializerInterface
    */
   private $serializer;

    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Framework\App\RequestInterface $request,
        CartRepositoryInterface $cartRepository,
        \Psr\Log\LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->request = $request;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    public function updateQuoteExtraField($cartId)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quoteId = $quoteIdMask->getQuoteId();
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($quoteId);
        $requestContent = $this->request->getContent();
        try {
            $data = $this->serializer->unserialize($requestContent);
            $extraFields = $this->serializer->serialize($data['sytelineExtraFields']);
            $quote->setSytelineCheckoutExtraFields($extraFields);
            $quote->setDataChanges(true);
            $quote->save();
        } catch (\Exception $ex) {
            $this->logger->critical("Couldn't save cart id: " . $quoteId . ", error msg: " . $ex->getMessage());
        }
    }
}
