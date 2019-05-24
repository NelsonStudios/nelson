<?php


namespace Fecon\OrsProducts\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddSkuPrefix extends Command
{

    /**
     * @var \Fecon\OrsProducts\Model\SkuPrefix 
     */
    protected $productUpdater;

    /**
     * Constructor
     *
     * @param \Fecon\OrsProducts\Model\SkuPrefix $productUpdater
     * @param string|null $name
     */
    public function __construct(
        \Fecon\OrsProducts\Model\SkuPrefix $productUpdater,
        $name = null
    ) {
        parent::__construct($name);
        $this->productUpdater = $productUpdater;
        
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->productUpdater->updateSkus($output);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("orsproducts:addskuprefix");
        $this->setDescription("Add a prefix to all ORS products");
        parent::configure();
    }
}