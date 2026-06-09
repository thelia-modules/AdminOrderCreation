<?php
/*************************************************************************************/
/*      This file is part of the module AdminOrderCreation                           */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace AdminOrderCreation\Hook\Back;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Model\OrderQuery;

class OrderEditHook extends OrderHook
{
    public static function getSubscribedHooks(): array
    {
        return [
            'order.edit-js' => [
                ['type' => 'back', 'method' => 'onOrderAddButtonJs'],
                ['type' => 'back', 'method' => 'onOrderJs'],
            ],
        ];
    }

    public function onOrderAddButtonJs(HookRenderEvent $event): void
    {
        $orderId = (int) $event->getArgument('order_id', null);
        $order = $orderId > 0 ? OrderQuery::create()->findPk($orderId) : null;

        $customerId = $order?->getCustomerId();
        $creditNoteId = $this->findReusableCreditNoteId($orderId);

        $event->add($this->render(
            'admin-order-creation/hook/orders.edit.js.html.twig',
            [
                'customerId' => $customerId,
                'creditNoteId' => $creditNoteId,
            ]
        ));
    }

    /**
     * Reproduces the legacy {loop type="credit-note" order_id=... used=false invoiced=true limit=1}.
     * The CreditNote module is optional: guard everything and never let a query-shape mismatch break the button.
     */
    private function findReusableCreditNoteId(int $orderId): ?int
    {
        if ($orderId <= 0 || !class_exists('\CreditNote\Model\OrderCreditNoteQuery')) {
            return null;
        }

        try {
            $creditNote = \CreditNote\Model\CreditNoteQuery::create()
                ->useOrderCreditNoteQuery()
                    ->filterByOrderId($orderId)
                ->endUse()
                ->useCreditNoteStatusQuery()
                    ->filterByUsed(false)
                    ->filterByInvoiced(true)
                ->endUse()
                ->findOne();

            return $creditNote?->getId();
        } catch (\Throwable) {
            return null;
        }
    }
}
