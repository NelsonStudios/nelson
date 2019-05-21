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

    /**
     * Constructor
     *
     * @param \Fecon\OrsProducts\Api\HandlerInterface $imporHandler
     */
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
        array_shift($rawData);  // Ignore first line of CSV file (headers)
        foreach ($rawData as $row) {
            $success = $this->imporHandler->processData($row, $message);
            if ($success) {
                $output->writeln("<info>" . $message . "</info>");
            } else {
                $output->writeln("<error>" . $message . "</error>");
            }
        }
        $output->writeln("\n\n<info>Job Finished</info>");
    }
}