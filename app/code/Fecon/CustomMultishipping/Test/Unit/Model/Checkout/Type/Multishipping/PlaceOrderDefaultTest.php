<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Test\Unit\Model\Checkout\Type\Multishipping;

use Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping\PlaceOrderDefault;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

class PlaceOrderDefaultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderManagement;

    /**
     * @var PlaceOrderDefault
     */
    private $placeOrderDefault;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->orderManagement = $this->getMockForAbstractClass(OrderManagementInterface::class);

        $this->placeOrderDefault = new PlaceOrderDefault($this->orderManagement);
    }

    public function testPlace()
    {
        $incrementId = '000000001';

        $order = $this->getMockForAbstractClass(OrderInterface::class);
        $order->method('getIncrementId')->willReturn($incrementId);
        $orderList = [$order];

        $this->orderManagement->expects($this->once())
            ->method('place')
            ->with($order)
            ->willReturn($order);
        $errors = $this->placeOrderDefault->place($orderList);

        $this->assertEmpty($errors);
    }

    public function testPlaceWithErrors()
    {
        $incrementId = '000000001';

        $order = $this->getMockForAbstractClass(OrderInterface::class);
        $order->method('getIncrementId')->willReturn($incrementId);
        $orderList = [$order];

        $exception = new \Exception('error');
        $this->orderManagement->method('place')->willThrowException($exception);
        $errors = $this->placeOrderDefault->place($orderList);

        $this->assertEquals(
            [$incrementId => $exception],
            $errors
        );
    }
}
