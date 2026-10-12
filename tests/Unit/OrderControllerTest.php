<?php

declare(strict_types=1);

namespace AdminOrderCreation\Tests\Unit;

use AdminOrderCreation\Controller\OrderController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The controller was written for the Thelia 2 back-office controller, which had a getDispatcher() the Thelia 3 one does
 * not: refunding the difference of a credit note larger than the order failed with "Call to undefined method" in the
 * middle of the creation of the order. Every method the controller calls on itself must exist.
 */
final class OrderControllerTest extends TestCase
{
    #[Test]
    public function everyMethodTheControllerCallsOnItselfExists(): void
    {
        $source = (string) file_get_contents((new \ReflectionClass(OrderController::class))->getFileName());
        preg_match_all('/\$this->([A-Za-z_]\w*)\(/', $source, $calls);

        $missing = array_values(array_filter(
            array_unique($calls[1]),
            static fn (string $method): bool => !method_exists(OrderController::class, $method),
        ));

        self::assertSame([], $missing, 'methods called on $this that the controller and its parents do not have');
    }
}
