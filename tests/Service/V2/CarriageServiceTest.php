<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\CarriageService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\CarriageService
 */
final class CarriageServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return CarriageService::class;
    }

    /**
     * @covers ::deliveryList
     */
    public function testDeliveryList(): void
    {
        $this->quickTest(
            'deliveryList',
            [
                [
                    'filter' => [
                        'delivery_method_id' => '123',
                        'departure_date'     => '2026-08-25',
                        // must be filtered out
                        'foo'                => 'bar',
                    ],
                    'cursor' => 'prev-cursor',
                    'limit'  => '50',
                ],
            ],
            [
                'POST',
                '/v2/carriage/delivery/list',
                '{"limit":50,"filter":{"delivery_method_id":123,"departure_date":"2026-08-25"},"cursor":"prev-cursor"}',
            ],
            '{"methods":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['methods' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::deliveryList
     */
    public function testDeliveryListDefaults(): void
    {
        $this->quickTest(
            'deliveryList',
            [],
            ['POST', '/v2/carriage/delivery/list', '{"limit":100}'],
            '{"methods":[]}',
            static function (array $result): void {
                self::assertSame(['methods' => []], $result);
            }
        );
    }
}
