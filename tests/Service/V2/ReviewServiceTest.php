<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\ReviewService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\ReviewService
 */
final class ReviewServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ReviewService::class;
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $json = <<<JSON
{
  "reviews": [
    {
      "id": "019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8",
      "sku": 160249683,
      "text": "Отличная книга",
      "rating": 5,
      "status": "NEW",
      "order_status": "DELIVERED",
      "published_at": "2026-08-01T10:00:00Z",
      "comments_amount": 0,
      "photos_amount": 1,
      "videos_amount": 0,
      "is_rating_participant": true
    }
  ],
  "last_id": "next-id",
  "has_next": true
}
JSON;

        $this->quickTest(
            'list',
            [
                [
                    'filters' => [
                        'status'         => 'NEW',
                        'order_status'   => 'DELIVERED',
                        'published_from' => '2026-08-01T00:00:00Z',
                        'published_to'   => '2026-08-08T00:00:00Z',
                        'skus'           => [160249683],
                        // must be filtered out
                        'foo'            => 'bar',
                    ],
                    'last_id'  => 'prev-id',
                    'limit'    => '50',
                    'sort_dir' => 'DESC',
                ],
            ],
            [
                'POST',
                '/v2/review/list',
                '{"limit":50,"sort_dir":"DESC","filters":{"status":"NEW","order_status":"DELIVERED","published_from":"2026-08-01T00:00:00Z","published_to":"2026-08-08T00:00:00Z","skus":["160249683"]},"last_id":"prev-id"}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::list
     */
    public function testListDefaults(): void
    {
        $this->quickTest(
            'list',
            [],
            [
                'POST',
                '/v2/review/list',
                '{"limit":100,"sort_dir":"ASC"}',
            ],
            '{"reviews":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['reviews' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::info
     */
    public function testInfo(): void
    {
        $json = '{"id":"019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8","sku":160249683,"rating":5,"status":"VIEWED"}';

        $this->quickTest(
            'info',
            ['019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8'],
            [
                'POST',
                '/v2/review/info',
                '{"review_id":"019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8"}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::count
     */
    public function testCount(): void
    {
        $json = '{"total":10,"new":3,"viewed":5,"processed":2}';

        $this->quickTest(
            'count',
            [],
            [
                'POST',
                '/v2/review/count',
                '{}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::changeStatus
     */
    public function testChangeStatus(): void
    {
        $this->quickTest(
            'changeStatus',
            [['019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8'], 'PROCESSED'],
            [
                'POST',
                '/v2/review/change-status',
                '{"review_ids":["019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8"],"status":"PROCESSED"}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::commentDelete
     */
    public function testCommentDelete(): void
    {
        $this->quickTest(
            'commentDelete',
            ['019c9c7a-b1a8-7cd6-a1a4-1b0b2a2b1c1d', 160249683],
            [
                'POST',
                '/v2/review/comment/delete',
                '{"comment_id":"019c9c7a-b1a8-7cd6-a1a4-1b0b2a2b1c1d","sku":160249683}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }
}
