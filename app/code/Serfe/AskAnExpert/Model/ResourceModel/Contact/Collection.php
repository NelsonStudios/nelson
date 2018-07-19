<?php

namespace Serfe\AskAnExpert\Model\ResourceModel\Contact;

use \Serfe\AskAnExpert\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'contact_id';

    protected $_previewFlag;

    protected function _construct()
    {
        $this->_init('Serfe\AskAnExpert\Model\Contact', 'Serfe\AskAnExpert\Model\ResourceModel\Contact');

        $this->_map['fields']['contact_id'] ='main_table.contact_id';
    }
}
