<?php


namespace Fecon\Shipping\Model\Preorder;

use Fecon\Shipping\Model\ResourceModel\Preorder\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    protected $collection;

    protected $dataPersistor;

    protected $loadedData;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->serializer = $serializer;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getId()] = $model->getData();
            if ($model->getData('shipping_method') != 'manualshipping_manualshipping') {
                $shippingMethods = $this->serializer->unserialize($model->getData('shipping_method'));
                $shippingData = [
                    'shipping_method' => $shippingMethods
                ];
                $this->loadedData[$model->getId()]['data'] = $shippingData;
            }
        }
        $data = $this->dataPersistor->get('fecon_shipping_preorder');

        if (!empty($data)) {
            
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('fecon_shipping_preorder');
        }
        
        return $this->loadedData;
    }
}
