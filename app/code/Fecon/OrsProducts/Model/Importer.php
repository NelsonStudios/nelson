<?php

namespace Fecon\OrsProducts\Model;

/**
 * Class to run and handle import task
 */
class Importer implements \Fecon\OrsProducts\Api\ImporterInterface
{

    /**
     * @var \Fecon\OrsProducts\Api\HandlerInterface
     */
    protected $imporHandler;

    public function __construct(
        \Fecon\OrsProducts\Api\HandlerInterface $imporHandler
    ) {
        $this->imporHandler = $imporHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function runImport($rawData, $output)
    {
        foreach ($rawData as $row) {
            $success = $this->imporHandler->processData($row);
        }
        $output->writeln("\n\n<info>Job Finished</info>");
    }
}