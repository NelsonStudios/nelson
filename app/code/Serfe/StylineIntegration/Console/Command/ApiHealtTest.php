<?php

namespace Serfe\StylineIntegration\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Check API healt
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class ApiHealtTest extends Command
{

    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";
    
    protected $client;
    
    public function __construct(
        \Serfe\StylineIntegration\Helper\SoapClient $client,
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
        $option = $input->getOption(self::NAME_OPTION);
        $output->writeln("Hello " . $name);
        $testData = $this->getPartInfoTestData();
        var_dump($this->client->getPartInfo($testData["PartNumber"], $testData["Quantity"], $testData["CustomerId"]));
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("serfe_stylineintegration:apihealttest");
        $this->setDescription("Test the configured API its working properly");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }
    
    protected function getPartInfoTestData()
    {
        return [
            "PartNumber" => "B10M-1.520SF-8.8",
            "Quantity" => "1",
            "CustomerId" => "C000037"
        ];
    }
}
