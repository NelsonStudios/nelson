<?php
namespace Fecon\AskAnExpert\Model\ResourceModel\Contact;

use \Fecon\AskAnExpert\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'contact_id';
    protected $_previewFlag;

    protected function _construct()
    {
        $this->_init('Fecon\AskAnExpert\Model\Contact', 'Fecon\AskAnExpert\Model\ResourceModel\Contact');
        $this->_map['fields']['contact_id'] ='main_table.contact_id';
    }
}