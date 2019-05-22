<?php


namespace Fecon\OrsProducts\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdatePrice extends \Fecon\OrsProducts\Console\Command\Import
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("orsproducts:updateprice");
        $this->setDescription("Update ORS products prices based on a configured percentage");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::REQUIRED, "File")
        ]);
    }
}
