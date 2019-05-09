<?php

namespace Fecon\OrsProducts\Api;

/**
 * Importer Interface
 */
interface ImporterInterface
{

    /**
     * Run import job
     *
     * @param array $rawData
     * @param OutputInterface $output
     * @return int
     */
    public function runImport($rawData, $output);
}