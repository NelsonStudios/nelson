<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ImportExport\Model\Import\Order;

use Firebear\ImportExport\Model\Import\Order\AbstractAdapter;

/**
 * Order Field Processor
 */
class FieldProcessor
{
    /**
     * Blob Format Column Names
     *
     * @var array
     */    
    protected $blobField = [
		'shipping_label'
	];
	
    /**
     * Explode Field
     *
     * @param string|array $data
     * @param string $separator     
     * @return array|boolean
     */
    public function explode($data, $separator = ',')
    {
        if (is_array($data)) {
			$keys = array_intersect(array_keys($data), $this->blobField);
			foreach ($keys as $key) {
				$data[$key] = base64_decode($data[$key]);
			}
			return $data;
        }

        $row = [];
        foreach (explode(',', $data) as $field) {
			$parts = explode('=', $field, 2);
			list($key, $value) = $parts;			
			$value = rawurldecode($value);
			if ($value && in_array($key, $this->blobField)) {
				$value = base64_decode($value);
			}
			$row[$key] = $value;
        }
        return $row;
    } 
}