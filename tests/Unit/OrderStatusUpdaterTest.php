<?php

declare(strict_types=1);

namespace AdminOrderCreation\Tests\Unit;

use AdminOrderCreation\Util\OrderStatusUpdater;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Order;

/**
 * The core compares the target status with the one of the order to move the stock: the order must still carry its
 * current status when ORDER_UPDATE_STATUS is dispatched.
 */
final class OrderStatusUpdaterTest extends TestCase
{
    #[Test]
    public function theEventIsDispatchedWhileTheOrderStillHasItsCurrentStatus(): void
    {
        $order = (new Order())->setStatusId(1);
        $seen = [];

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(TheliaEvents::ORDER_UPDATE_STATUS, static function (OrderEvent $event) use (&$seen): void {
            $seen = ['current' => $event->getOrder()->getStatusId(), 'target' => $event->getStatus()];
        });

        (new OrderStatusUpdater($dispatcher))->update($order, 2);

        self::assertSame(['current' => 1, 'target' => 2], $seen);
    }

    #[Test]
    public function theUpdaterLeavesTheNewStatusToTheListeners(): void
    {
        $order = (new Order())->setStatusId(1);

        (new OrderStatusUpdater(new EventDispatcher()))->update($order, 2);

        self::assertSame(1, $order->getStatusId());
    }
}
