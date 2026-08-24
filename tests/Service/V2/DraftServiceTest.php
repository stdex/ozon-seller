<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\DraftService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\DraftService
 */
final class DraftServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return DraftService::class;
    }

    /**
     * @covers ::createInfo
     */
    public function testCreateInfo(): void
    {
        $this->quickTest(
            'createInfo',
            [1],
            ['POST', '/v2/draft/create/info', '{"draft_id":1}'],
            '{"status":"SUCCESS","clusters":[]}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS', 'clusters' => []], $result);
            }
        );
    }

    /**
     * @covers ::timeslotInfo
     */
    public function testTimeslotInfo(): void
    {
        $this->quickTest(
            'timeslotInfo',
            [
                [
                    'draft_id'                    => '1',
                    'date_from'                   => '2026-08-25T00:00:00Z',
                    'date_to'                     => '2026-08-28T00:00:00Z',
                    'supply_type'                 => 'DIRECT',
                    'selected_cluster_warehouses' => [['warehouse_id' => 123]],
                    // must be filtered out
                    'foo'                         => 'bar',
                ],
            ],
            [
                'POST',
                '/v2/draft/timeslot/info',
                '{"draft_id":1,"date_from":"2026-08-25T00:00:00Z","date_to":"2026-08-28T00:00:00Z","supply_type":"DIRECT","selected_cluster_warehouses":[{"warehouse_id":123}]}',
            ],
            '{"result":{"requested_date_from":"2026-08-25T00:00:00Z"}}',
            static function (array $result): void {
                self::assertSame('2026-08-25T00:00:00Z', $result['result']['requested_date_from']);
            }
        );
    }

    /**
     * @covers ::supplyCreate
     */
    public function testSupplyCreate(): void
    {
        $this->quickTest(
            'supplyCreate',
            [
                [
                    'draft_id'                    => '1',
                    'supply_type'                 => 'DIRECT',
                    'selected_cluster_warehouses' => [['warehouse_id' => 123]],
                    'timeslot'                    => [
                        'from_in_timezone' => '2026-08-25T10:00:00+03:00',
                        'to_in_timezone'   => '2026-08-25T12:00:00+03:00',
                        // must be filtered out
                        'foo'              => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v2/draft/supply/create',
                '{"draft_id":1,"supply_type":"DIRECT","selected_cluster_warehouses":[{"warehouse_id":123}],"timeslot":{"from_in_timezone":"2026-08-25T10:00:00+03:00","to_in_timezone":"2026-08-25T12:00:00+03:00"}}',
            ],
            '{"draft_id":1}',
            static function (array $result): void {
                self::assertSame(['draft_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::supplyCreateStatus
     */
    public function testSupplyCreateStatus(): void
    {
        $this->quickTest(
            'supplyCreateStatus',
            [1],
            ['POST', '/v2/draft/supply/create/status', '{"draft_id":1}'],
            '{"status":"SUCCESS","order_id":123}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS', 'order_id' => 123], $result);
            }
        );
    }
}
