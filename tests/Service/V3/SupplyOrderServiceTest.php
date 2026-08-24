<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V3;

use Gam6itko\OzonSeller\Service\V3\SupplyOrderService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V3\SupplyOrderService
 */
final class SupplyOrderServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return SupplyOrderService::class;
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $this->quickTest(
            'list',
            [
                [
                    'filter' => [
                        'states'                => ['ORDER_STATE_DATA_FILLING'],
                        'dropoff_warehouse_ids' => [123],
                        'order_number_search'   => '123',
                        'timeslot_from_range'   => [
                            'from'                 => '2026-08-01T00:00:00Z',
                            'to'                   => '2026-08-08T00:00:00Z',
                            'timeslot_filter_type' => 'LOCAL',
                            // must be filtered out
                            'foo'                  => 'bar',
                        ],
                        // must be filtered out
                        'baz' => 'qux',
                    ],
                    'limit'    => '50',
                    'last_id'  => 'prev-id',
                    'sort_by'  => 'TIMESLOT_FROM_UTC',
                    'sort_dir' => 'DESC',
                ],
            ],
            [
                'POST',
                '/v3/supply-order/list',
                '{"limit":50,"sort_by":"TIMESLOT_FROM_UTC","sort_dir":"DESC","filter":{"states":["ORDER_STATE_DATA_FILLING"],"dropoff_warehouse_ids":["123"],"order_number_search":"123","timeslot_from_range":{"from":"2026-08-01T00:00:00Z","to":"2026-08-08T00:00:00Z","timeslot_filter_type":"LOCAL"}},"last_id":"prev-id"}',
            ],
            '{"order_ids":[],"last_id":""}',
            static function (array $result): void {
                self::assertSame(['order_ids' => [], 'last_id' => ''], $result);
            }
        );
    }

    /**
     * @covers ::get
     */
    public function testGet(): void
    {
        $this->quickTest(
            'get',
            [[123, '456']],
            ['POST', '/v3/supply-order/get', '{"order_ids":["123","456"]}'],
            '{"orders":[]}',
            static function (array $result): void {
                self::assertSame(['orders' => []], $result);
            }
        );
    }
}
