<?php
/*************************************************************************************/
/*      This file is part of the module AdminOrderCreation                           */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace AdminOrderCreation\Hook\Back;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class OrderHook extends BaseHook
{
    public static function getSubscribedHooks(): array
    {
        return [
            'orders.top' => [
                ['type' => 'back', 'method' => 'onOrdersTableHeader'],
            ],
            'orders.js' => [
                ['type' => 'back', 'method' => 'onOrderJs'],
            ],
        ];
    }

    public function onOrdersTableHeader(HookRenderEvent $event): void
    {
        $event->add($this->render(
            'admin-order-creation/hook/orders.table-header.html.twig',
            $event->getArguments()
        ));
    }

    public function onOrderJs(HookRenderEvent $event): void
    {
        $event->add($this->render(
            'admin-order-creation/hook/orders.js.html.twig',
            $event->getArguments()
        ));
    }
}
