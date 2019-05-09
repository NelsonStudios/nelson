<?php

namespace Fecon\OrsProducts\Api;

/**
 * Data Parser Interface
 */
interface DataParserInterface
{

    const CSV_DIRECTORY = '/var/orsimport/';

    /**
     * Reads the data from CSV file and returns an array with it
     *
     * @param string $fileName
     * @return array|boolean    Returns false if file does not exists
     */
    public function readCsv($fileName);
}