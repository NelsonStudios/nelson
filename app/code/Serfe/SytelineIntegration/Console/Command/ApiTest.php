<?php

namespace Serfe\SytelineIntegration\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test Syteline API
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class ApiTest extends Command
{

    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";
    
    protected $client;
    
    /**
     * Constructor
     *
     * @param \Serfe\SytelineIntegration\Helper\SoapClient $client
     * @param mixed $name
     */
    public function __construct(
        \Serfe\SytelineIntegration\Helper\ApiHelper $client,
        $name = null
    ) {
        parent::__construct($name);
        $this->client = $client;
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
        $this->setName("serfe:syteline:test");
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
            "cartLine" => [
                "PartNumber" => "B10M-1.520SF-8.8",
                "Quantity" => "1",
                "UOM" => "EA",
                "Line" => "0"
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
                "DigabitERPTransactionStatus" => "SUBMITTED"
            ]
        ];
    }
}
