<?php

namespace DevPhase\Feeds\Block;

/**
 * Description of InstagramWidget
 *
 * @author  <fecon.com>
 */
class InstagramWidget extends \Magento\Framework\View\Element\Template
{
    const INSTAGRAM_ENDPOINT = 'feeds/instagram';

    public function getFeedUrl() {
        return $this::INSTAGRAM_ENDPOINT;
    }

    public function isNoBounce() {
        return true;
    }
}