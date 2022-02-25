<?php

namespace Core\MageplazaSlider\Model;

use Magento\Framework\Event\ObserverInterface;    

class Observer implements ObserverInterface
{    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getData('response');
        if (!$response)
            return;
        $html = $response->getBody();
        if ($html == '')
            return;
        $conditionalJsPattern = '@(?:<script type="text/javascript"|<script)(.*)</script>@msU';
        preg_match_all($conditionalJsPattern, $html, $_matches);
        $if_js = implode('', $_matches[0]);
        $html = preg_replace($conditionalJsPattern, '', $html);
        $html .= $if_js;
        $response->setBody($html);
    }
}