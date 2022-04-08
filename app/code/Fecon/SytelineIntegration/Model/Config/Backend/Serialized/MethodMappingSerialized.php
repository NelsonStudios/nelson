<?php

namespace Fecon\SytelineIntegration\Model\Config\Backend\Serialized;

class MethodMappingSerialized extends \Magento\Config\Model\Config\Backend\Serialized
{
    /**
     * Unset array element with '__empty' key
     *
     * @return \SmartOsc\RequestForm\Model\Config\Backend\Serialized\ArraySerialized
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $data = [];

        if (is_array($value)) {
            unset($value['__empty']);
        }

        foreach ($value as $item) {
            $data[] = $item;
        }

        $this->setValue($data);
        return parent::beforeSave();
    }
}
