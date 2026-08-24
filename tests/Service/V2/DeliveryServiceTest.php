<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\DeliveryService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\DeliveryService
 */
final class DeliveryServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return DeliveryService::class;
    }

    /**
     * @covers ::checkout
     */
    public function testCheckout(): void
    {
        $this->quickTest(
            'checkout',
            [
                [
                    'buyer_phone'     => '+79001234567',
                    'delivery_schema' => 'FBS',
                    'delivery_type'   => ['courier' => ['address' => 'Москва']],
                    'items'           => [['sku' => 160249683, 'quantity' => 1]],
                    // must be filtered out
                    'foo'             => 'bar',
                ],
            ],
            [
                'POST',
                '/v2/delivery/checkout',
                '{"buyer_phone":"+79001234567","delivery_schema":"FBS","delivery_type":{"courier":{"address":"Москва"}},"items":[{"sku":160249683,"quantity":1}]}',
            ],
            '{"splits":[]}',
            static function (array $result): void {
                self::assertSame(['splits' => []], $result);
            }
        );
    }
}
