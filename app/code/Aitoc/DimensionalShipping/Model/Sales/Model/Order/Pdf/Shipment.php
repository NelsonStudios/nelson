<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */

/**
 * Copyright © 2017 Aitoc. All rights reserved.
 */
namespace Aitoc\DimensionalShipping\Model\Sales\Model\Order\Pdf;

use Aitoc\DimensionalShipping\Helper\Data as DimensionalShippingHelper;
use Aitoc\DimensionalShipping\Model\ResourceModel\OrderBox\CollectionFactory as OrderBoxCollectionFactory;
use Aitoc\DimensionalShipping\Model\ResourceModel\OrderItemBox\CollectionFactory as OrderItemBoxCollectionFactory;
use Magento\Sales\Model\Order\Pdf\Config;

/**
 * Class Shipment
 *
 * Generate PDF with shipment details
 */
class Shipment extends \Magento\Sales\Model\Order\Pdf\Shipment
{
    /**
     * @var OrderBoxCollectionFactory
     */
    protected $orderBoxCollectionFactory;

    /**
     * @var OrderItemBoxCollectionFactory
     */
    protected $orderItemBoxCollectionFactory;

    /**
     * @var DimensionalShippingHelper
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Model\Order\ItemRepository
     */
    protected $itemRepository;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * Shipment constructor.
     *
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Config $pdfConfig
     * @param Total\Factory $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param OrderBoxCollectionFactory $orderBoxCollectionFactory
     * @param OrderItemBoxCollectionFactory $orderItemBoxCollectionFactory
     * @param DimensionalShippingHelper $helper
     * @param \Magento\Sales\Model\Order\ItemRepository $itemRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystemItemsFactory,
        Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        OrderBoxCollectionFactory $orderBoxCollectionFactory,
        OrderItemBoxCollectionFactory $orderItemBoxCollectionFactory,
        DimensionalShippingHelper $helper,
        \Magento\Sales\Model\Order\ItemRepository $itemRepository,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystemItemsFactory,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $appEmulation,
            $data
        );
        $this->orderBoxCollectionFactory     = $orderBoxCollectionFactory;
        $this->orderItemBoxCollectionFactory = $orderItemBoxCollectionFactory;
        $this->helper                        = $helper;
        $this->itemRepository                = $itemRepository;
    }

    /**
     * Return PDF document with box details
     *
     * @param array $shipments
     * @return \Zend_Pdf
     * @throws \Zend_Pdf_Exception
     */
    public function getPdf($shipments = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                $this->appEmulation->startEnvironmentEmulation(
                    $shipment->getStoreId(),
                    \Magento\Framework\App\Area::AREA_FRONTEND,
                    true
                );
                $this->_storeManager->setCurrentStore($shipment->getStoreId());
            }
            $page = $this->newPage();
            $order = $shipment->getOrder();
            /* Add image */
            $this->insertLogo($page, $shipment->getStore());
            /* Add address */
            $this->insertAddress($page, $shipment->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $shipment,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Packing Slip # ') . $shipment->getIncrementId());
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            $this->_drowBoxInfo($page, $order, $shipment, $pdf);

            if ($shipment->getStoreId()) {
                $this->appEmulation->stopEnvironmentEmulation();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Include box details in PDF
     *
     * @param \Zend_Pdf_Page $page
     * @param $order
     * @param $shipment
     * @param $pdf
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Pdf_Exception
     */
    protected function _drowBoxInfo(\Zend_Pdf_Page $page, $order, $shipment, &$pdf)
    {
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));


        $page->drawText(
            __('Order Boxes: '),
            35,
            $this->y - 9,
            'UTF-8'
        );
        $this->y -= 18;

        $orderBoxCollection = $this->orderBoxCollectionFactory->create()
            ->addFieldToFilter('order_id', $order->getId())
            ->getItems();
        foreach ($orderBoxCollection as $orderBox) {
            $box = $this->helper->getBoxById($orderBox->getBoxId());
            $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $page->setLineWidth(0.5);
            $page->drawText(
                __('Box: ') . $box->getName(),
                35,
                $this->y -= 8,
                'UTF-8'
            );
            $this->y -= 5;

            /* Add table head */
            $this->_setFontRegular($page, 10);
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y - 15);
            $this->y -= 10;
            $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

            //columns headers
            $lines[0][] = ['text' => __('Products'), 'feed' => 35];

            $lines[0][] = ['text' => __('SKU'), 'feed' => 290, 'align' => 'right'];

            $lines[0][] = ['text' => __('QTY'), 'feed' => 365, 'align' => 'right'];

            $lineBlock = ['lines' => $lines, 'height' => 5];

            $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);

            $orderItemBoxCollection = $this->orderItemBoxCollectionFactory->create()
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('order_box_id', $orderBox->getId())
                ->addGroupByNameField('sku')
                ->getItems();

            $this->y -= 30;
            foreach ($orderItemBoxCollection as $orderBoxItem) {
                $items = $order->getAllItems();
                $item  = null;

                foreach ($shipment->getAllItems() as $orderItem) {
                    if ($orderItem->getOrderItem()->getParentItem()) {
                        continue;
                    }
                }
                $item = $this->itemRepository->get($orderBoxItem->getOrderItemId());
                $orderItemBoxCollectionCount = $this->orderItemBoxCollectionFactory->create()
                    ->addFieldToFilter('order_id', $order->getId())
                    ->addFieldToFilter('order_box_id', $orderBox->getId())
                    ->addFieldToFilter('sku', $item->getSku())
                    ->count();

                // draw Product name
                $lines2[0]   = [['text' => $this->string->split($item->getName(), 35, true, true), 'feed' => 35]];
                $lines2[0][] = [
                    'text'  => $this->string->split($item->getSku(), 17),
                    'feed'  => 290,
                    'align' => 'right',
                ];
                $lines2[0][] = [
                    'text'  => $this->string->split($orderItemBoxCollectionCount, 17),
                    'feed'  => 365,
                    'align' => 'right',
                ];

                $lineBlock2 = ['lines' => $lines2, 'height' => 20];

                $this->drawLineBlocks($page, [$lineBlock2], ['table_header' => true]);
                //$page = end($pdf->pages);
            }
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
    }
}
