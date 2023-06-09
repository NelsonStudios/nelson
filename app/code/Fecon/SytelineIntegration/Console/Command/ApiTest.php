<?php

namespace Fecon\SytelineIntegration\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test Syteline API
 *
 * 
 */
class ApiTest extends Command
{
    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";
    
    protected $client;

    protected $testHelper;

    protected $orderRepository;
    
    /**
     * Constructor
     *
     * @param \Fecon\SytelineIntegration\Helper\ApiHelper $client
     * @param \Fecon\SytelineIntegration\Helper\SytelineHelper $testHelper
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Magento\Framework\App\State $state
     * @param mixed $name
     */
    public function __construct(
        \Fecon\SytelineIntegration\Helper\ApiHelper $client,
        \Fecon\SytelineIntegration\Helper\SytelineHelper $testHelper,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\App\State $state,
        $name = null
    ) {
       // $state->setAreaCode('frontend');
        parent::__construct($name);
        $this->client = $client;
        $this->testHelper = $testHelper;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $name = $input->getArgument(self::NAME_ARGUMENT);
        if ($name == 'types') {
            $outputData = $this->client->getSoapTypes();
        } elseif ($name == 'GetCart') {
            $testData = $this->getCartTestData();
            $response = $this->client->getCart($testData);
            $outputData = print_r($response, true);
        } elseif ($name == 'TestGetCart') {
            $order = $this->getOrder();
            $this->testHelper->submitCartToSyteline($order);
        } else {
            $testData = $this->getPartInfoTestData();
            $response = $this->client->getPartInfo($testData);
            $outputData = print_r($response, true);
        }
        
        $output->writeln($outputData);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("fecon:syteline:test");
        $this->setDescription("Test the configured API its working properly");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }

    /**
     * Get test data for the SOAP call
     *
     * @return array
     */
    protected function getPartInfoTestData()
    {
        return [
            "PartNumber" => "B10M-1.520SF-8.8",
            "Quantity" => '1',
            "CustomerId" => "C000037"
        ];
    }
    
    /**
     * Get test data for the SOAP call
     *
     * @return array
     */
    protected function getCartTestData()
    {
        return [
            "address" => [
                "CustomerId" => "C000037",
                "Line1" => "240 Hookhi St",
                "Line2" => "",
                "Line3" => "",
                "City" => "Wailuku",
                "State" => "HI",
                "Zipcode" => "96793",
                "Country" => "United States"
            ],
            "cartLines" => [
                [
                    "PartNumber" => "B10M-1.520SF-8.8",
                    "Quantity" => "1",
                    "UOM" => "EA",
                    "Line" => "0"
                ]
            ],
            "request" => [
                "Comments" => "Test order",
                "EmailAddress" => "daniel@digabit.com",
                "AccountNumber" => "123456",
                "ShipVia" => "BEST",
                "OrderCustomerName" => "Daniel",
                "CollectAccountNumber" => "123456",
                "OrderStock" => "Yes",
                "OrderPhoneNumber" => "3031234567",
                "DigabitERPTransactionType" => "Order",
                "DigabitERPTransactionStatus" => "SUBMITTED",
                "OrderIncrementId" => "000000122"
            ]
        ];
    }

    protected function getOrder()
    {
        $order = $this->orderRepository->get('1');
        
        return $order;
    }
}
