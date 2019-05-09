<?php

namespace Fecon\OrsProducts\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command
{

    const NAME_ARGUMENT = "file";

    /**
     * @var \Fecon\OrsProducts\Api\DataParserInterface
     */
    protected $dataParser;

    public function __construct(
        \Fecon\OrsProducts\Api\DataParserInterface $dataParser,
        $name = null
    ) {
        $this->dataParser = $dataParser;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $file = $input->getArgument(self::NAME_ARGUMENT);
        
        $csvRawData = $this->dataParser->readCsv($file);
        if ($csvRawData === false) {
            $output->writeln("<error>File " . $file . " does not exists</error>");
            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("orsproducts:import");
        $this->setDescription("Update ORS products based on a spreadsheet data");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::REQUIRED, "File")
        ]);
        parent::configure();
    }
}
