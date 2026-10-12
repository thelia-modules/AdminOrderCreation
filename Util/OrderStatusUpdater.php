<?php
/*************************************************************************************/
/*      This file is part of the module AdminOrderCreation                           */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace AdminOrderCreation\Util;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Order;

/**
 * Changes the status of a saved order through ORDER_UPDATE_STATUS.
 *
 * The order keeps its current status while the event is dispatched: the core action compares the target status with
 * the one of the order to move the stock (and the listeners of the other modules read the previous status the same
 * way), then writes the new status itself. Setting the target status on the order first makes the change invisible.
 */
final class OrderStatusUpdater
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function update(Order $order, int $statusId): void
    {
        $this->eventDispatcher->dispatch(
            (new OrderEvent($order))->setStatus($statusId),
            TheliaEvents::ORDER_UPDATE_STATUS
        );
    }
}
