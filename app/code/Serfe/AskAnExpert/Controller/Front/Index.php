<?php
namespace Serfe\AskAnExpert\Controller\Front;

class Index extends \Serfe\AskAnExpert\Controller\Front
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
