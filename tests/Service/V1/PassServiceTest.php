<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\PassService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\PassService
 */
final class PassServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return PassService::class;
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
                        'arrival_pass_ids'   => [10],
                        'arrival_reason'     => 'FBS_DELIVERY',
                        'dropoff_point_ids'  => [456],
                        'warehouse_ids'      => [123],
                        'only_active_passes' => true,
                        // must be filtered out
                        'foo'                => 'bar',
                    ],
                    'cursor' => 'prev-cursor',
                    'limit'  => '50',
                ],
            ],
            [
                'POST',
                '/v1/pass/list',
                '{"limit":50,"filter":{"arrival_pass_ids":["10"],"arrival_reason":"FBS_DELIVERY","dropoff_point_ids":["456"],"warehouse_ids":["123"],"only_active_passes":true},"cursor":"prev-cursor"}',
            ],
            '{"arrival_passes":[],"cursor":""}',
            static function (array $result): void {
                self::assertSame(['arrival_passes' => [], 'cursor' => ''], $result);
            }
        );
    }
}
