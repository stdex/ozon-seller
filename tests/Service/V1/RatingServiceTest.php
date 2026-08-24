<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\RatingService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\RatingService
 */
final class RatingServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return RatingService::class;
    }

    /**
     * @covers ::summary
     */
    public function testSummary(): void
    {
        $json = '{"groups":[{"group_name":"Качество","items":[]}],"premium":true,"premium_plus":false,"penalty_score_exceeded":false}';

        $this->quickTest(
            'summary',
            [],
            ['POST', '/v1/rating/summary', '{}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::history
     */
    public function testHistory(): void
    {
        $this->quickTest(
            'history',
            [
                [
                    'date_from'           => '2026-08-01T00:00:00Z',
                    'date_to'             => '2026-08-08T00:00:00Z',
                    'ratings'             => ['rating_on_time'],
                    'with_premium_scores' => true,
                    // must be filtered out
                    'foo'                 => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/rating/history',
                '{"date_from":"2026-08-01T00:00:00Z","date_to":"2026-08-08T00:00:00Z","ratings":["rating_on_time"],"with_premium_scores":true}',
            ],
            '{"ratings":[],"premium_scores":[]}',
            static function (array $result): void {
                self::assertSame(['ratings' => [], 'premium_scores' => []], $result);
            }
        );
    }

    /**
     * @covers ::indexFbsInfo
     */
    public function testIndexFbsInfo(): void
    {
        $json = '{"index":0.5,"currency_code":"RUB","defects":[]}';

        $this->quickTest(
            'indexFbsInfo',
            [],
            ['POST', '/v1/rating/index/fbs/info', '{}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::indexFbsPostingList
     */
    public function testIndexFbsPostingList(): void
    {
        $this->quickTest(
            'indexFbsPostingList',
            [
                [
                    'filter' => [
                        'date_from'       => '2026-08-01T00:00:00Z',
                        'date_to'         => '2026-08-08T00:00:00Z',
                        'posting_numbers' => ['33920474-0032-1'],
                        // must be filtered out
                        'foo'             => 'bar',
                    ],
                    'cursor' => 'prev-cursor',
                    'limit'  => '50',
                ],
            ],
            [
                'POST',
                '/v1/rating/index/fbs/posting/list',
                '{"limit":50,"filter":{"date_from":"2026-08-01T00:00:00Z","date_to":"2026-08-08T00:00:00Z","posting_numbers":["33920474-0032-1"]},"cursor":"prev-cursor"}',
            ],
            '{"errors":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['errors' => [], 'has_next' => false], $result);
            }
        );
    }
}
