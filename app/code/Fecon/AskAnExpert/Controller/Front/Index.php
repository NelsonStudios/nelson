<?php
namespace Fecon\AskAnExpert\Controller\Front;

class Index extends \Fecon\AskAnExpert\Controller\Front
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
