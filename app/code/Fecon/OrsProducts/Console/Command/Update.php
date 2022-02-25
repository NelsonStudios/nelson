<?php

namespace Fecon\OrsProducts\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends Command
{

    /**
     * @var \Fecon\OrsProducts\Model\UpdateProducts 
     */
    protected $productUpdater;

    /**
     * Constructor
     *
     * @param \Fecon\OrsProducts\Model\UpdateProducts $productUpdater
     * @param \Magento\Framework\App\State $state
     * @param string|null $name
     */
    public function __construct(
        \Fecon\OrsProducts\Model\UpdateProducts $productUpdater,
        \Magento\Framework\App\State $state,
        $name = null
    ) {
        parent::__construct($name);
        $this->productUpdater = $productUpdater;
        // $state->setAreaCode('adminhtml');
        
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->productUpdater->updateProductsVisibility($output);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("orsproducts:update_visiblity");
        $this->setDescription("Update ORS products visibility");
        parent::configure();
    }
}