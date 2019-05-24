<?php

namespace Fecon\OrsProducts\Console\Command;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Description of UpdateImage
 */
class UpdateImage extends \Fecon\OrsProducts\Console\Command\Import
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("orsproducts:updateimage");
        $this->setDescription("Update ORS products image");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::REQUIRED, "File")
        ]);
    }
}