<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\AnalyticsService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\AnalyticsService
 */
final class AnalyticsServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return AnalyticsService::class;
    }

    /**
     * @covers ::turnoverStocks
     */
    public function testTurnoverStocks(): void
    {
        $this->quickTest(
            'turnoverStocks',
            [
                [
                    'sku'    => [160249683],
                    'limit'  => '50',
                    'offset' => '10',
                    // must be filtered out
                    'foo'    => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/analytics/turnover/stocks',
                '{"limit":50,"offset":10,"sku":["160249683"]}',
            ],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
            }
        );
    }

    /**
     * @covers ::stocks
     */
    public function testStocks(): void
    {
        $this->quickTest(
            'stocks',
            [
                [
                    'skus'                 => [160249683],
                    'cluster_ids'          => [1],
                    'warehouse_ids'        => [123],
                    'item_tags'            => ['ECONOM'],
                    'unmarked_stocks_only' => true,
                    // must be filtered out
                    'foo'                  => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/analytics/stocks',
                '{"skus":["160249683"],"cluster_ids":["1"],"warehouse_ids":["123"],"item_tags":["ECONOM"],"unmarked_stocks_only":true}',
            ],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
            }
        );
    }

    /**
     * @covers ::manageStocks
     */
    public function testManageStocks(): void
    {
        $this->quickTest(
            'manageStocks',
            [
                [
                    'filter' => [
                        'skus'          => [160249683],
                        'warehouse_ids' => [123],
                        'stock_types'   => ['STOCK_TYPE_VALID'],
                        // must be filtered out
                        'foo'           => 'bar',
                    ],
                    'limit'  => '50',
                    'offset' => '10',
                ],
            ],
            [
                'POST',
                '/v1/analytics/manage/stocks',
                '{"limit":50,"offset":10,"filter":{"skus":["160249683"],"warehouse_ids":["123"],"stock_types":["STOCK_TYPE_VALID"]}}',
            ],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
            }
        );
    }

    /**
     * @covers ::productQueries
     */
    public function testProductQueries(): void
    {
        $this->quickTest(
            'productQueries',
            [
                [
                    'skus'      => [160249683],
                    'date_from' => '2026-08-01',
                    'date_to'   => '2026-08-08',
                    'sort_by'   => 'BY_VIEWS',
                    'sort_dir'  => 'DESCENDING',
                    // must be filtered out
                    'foo'       => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/analytics/product-queries',
                '{"page":1,"page_size":100,"skus":["160249683"],"date_from":"2026-08-01","date_to":"2026-08-08","sort_by":"BY_VIEWS","sort_dir":"DESCENDING"}',
            ],
            '{"items":[],"total":0}',
            static function (array $result): void {
                self::assertSame(['items' => [], 'total' => 0], $result);
            }
        );
    }

    /**
     * @covers ::productQueriesDetails
     */
    public function testProductQueriesDetails(): void
    {
        $this->quickTest(
            'productQueriesDetails',
            [
                [
                    'skus'         => [160249683],
                    'date_from'    => '2026-08-01',
                    'limit_by_sku' => '5',
                    'page'         => '2',
                    'page_size'    => '50',
                ],
            ],
            [
                'POST',
                '/v1/analytics/product-queries/details',
                '{"page":2,"page_size":50,"skus":["160249683"],"date_from":"2026-08-01","limit_by_sku":5}',
            ],
            '{"queries":[],"total":0}',
            static function (array $result): void {
                self::assertSame(['queries' => [], 'total' => 0], $result);
            }
        );
    }
}
