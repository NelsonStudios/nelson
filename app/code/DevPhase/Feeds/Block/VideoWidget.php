<?php

namespace DevPhase\Feeds\Block;

/**
 * Description of VideoWidget
 *
 * @author  <fecon.com>
 */
class VideoWidget extends \Magento\Framework\View\Element\Template
{
    const VIDEO_ENDPOINT = 'feeds/videos';

    public function getFeedUrl() {
        return $this::VIDEO_ENDPOINT;
    }

    public function isNoBounce() {
        return true;
    }
}