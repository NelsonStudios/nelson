<?php

namespace Serfe\AskAnExpert\Controller\Front;

class Index extends \Serfe\AskAnExpert\Controller\Front
{

    public function execute()
    {
    
        //echo "news module";
        $this->_view->loadLayout();

        //$this->_view->getLayout()->initMessages();

        $this->_view->renderLayout();
    }
}
