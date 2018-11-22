<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Test\Unit\Controller\Checkout;

use Fecon\CustomMultishipping\Controller\Checkout\Plugin;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var Plugin
     */
    protected $object;

    protected function setUp()
    {
        $this->cartMock = $this->createMock(\Magento\Checkout\Model\Cart::class);
        $this->quoteMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['__wakeUp', 'setIsMultiShipping']
        );
        $this->cartMock->expects($this->once())->method('getQuote')->will($this->returnValue($this->quoteMock));
        $this->object = new \Fecon\CustomMultishipping\Controller\Checkout\Plugin($this->cartMock);
    }

    public function testExecuteTurnsOffMultishippingModeOnQuote()
    {
        $subject = $this->createMock(\Magento\Checkout\Controller\Index\Index::class);
        $this->quoteMock->expects($this->once())->method('setIsMultiShipping')->with(0);
        $this->object->beforeExecute($subject);
    }
}
