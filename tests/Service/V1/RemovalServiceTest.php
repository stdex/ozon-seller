<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\RemovalService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\RemovalService
 */
final class RemovalServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return RemovalService::class;
    }

    /**
     * @covers ::fromSupplyList
     */
    public function testFromSupplyList(): void
    {
        $this->quickTest(
            'fromSupplyList',
            ['2026-08-01T00:00:00Z', '2026-08-08T00:00:00Z', 50, 'prev-id'],
            [
                'POST',
                '/v1/removal/from-supply/list',
                '{"date_from":"2026-08-01T00:00:00Z","date_to":"2026-08-08T00:00:00Z","limit":50,"last_id":"prev-id"}',
            ],
            '{"returns_summary_report_rows":[],"last_id":""}',
            static function (array $result): void {
                self::assertSame(['returns_summary_report_rows' => [], 'last_id' => ''], $result);
            }
        );
    }

    /**
     * @covers ::fromStockList
     */
    public function testFromStockList(): void
    {
        $this->quickTest(
            'fromStockList',
            ['2026-08-01T00:00:00Z', '2026-08-08T00:00:00Z'],
            [
                'POST',
                '/v1/removal/from-stock/list',
                '{"date_from":"2026-08-01T00:00:00Z","date_to":"2026-08-08T00:00:00Z","limit":100}',
            ],
            '{"returns_summary_report_rows":[]}',
            static function (array $result): void {
                self::assertSame(['returns_summary_report_rows' => []], $result);
            }
        );
    }
}
