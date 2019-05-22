<?php


namespace Fecon\OrsProducts\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Delete extends Command
{

    const NAME_ARGUMENT = "file";

    /**
     * @var \Fecon\OrsProducts\Api\DataParserInterface
     */
    protected $dataParser;

    /**
     * @var \Fecon\OrsProducts\Model\DeleteProducts 
     */
    protected $productEraser;

    /**
     * Constructor
     *
     * @param \Fecon\OrsProducts\Model\DeleteProducts $productEraser
     * @param \Fecon\OrsProducts\Api\DataParserInterface $dataParser
     * @param string|null $name
     */
    public function __construct(
        \Fecon\OrsProducts\Model\DeleteProducts $productEraser,
        \Fecon\OrsProducts\Api\DataParserInterface $dataParser,
        $name = null
    ) {
        parent::__construct($name);
        $this->productEraser = $productEraser;
        $this->dataParser = $dataParser;
        
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
        } else {
            $this->productEraser->deleteProducts($output, $csvRawData);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("orsproducts:delete");
        $this->setDescription("Delete ORS products that have not been update in the last date update");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::REQUIRED, "File")
        ]);
        parent::configure();
    }
}
