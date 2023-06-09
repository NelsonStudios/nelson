<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Fecon\CustomMultishipping\Test\Unit\Block\Checkout\Billing;

class ItemsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Fecon\CustomMultishipping\Block\Checkout\Billing\Items
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->model = $objectManager->getObject(
            \Fecon\CustomMultishipping\Block\Checkout\Billing\Items::class,
            [
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    public function testGetVirtualProductEditUrl()
    {
        $url = 'http://example.com';
        $this->urlBuilderMock->expects($this->once())->method('getUrl')->with('checkout/cart', [])->willReturn($url);
        $this->assertEquals($url, $this->model->getVirtualProductEditUrl());
    }
}
