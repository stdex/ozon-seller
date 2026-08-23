<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\ReviewService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\ReviewService
 */
final class ReviewServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ReviewService::class;
    }

    /**
     * @covers ::commentCreate
     */
    public function testCommentCreate(): void
    {
        $json = '{"comment_id":"019c9c7a-b1a8-7cd6-a1a4-1b0b2a2b1c1d"}';

        $this->quickTest(
            'commentCreate',
            [
                [
                    'review_id'                => '019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8',
                    'text'                     => 'Спасибо за отзыв!',
                    'mark_review_as_processed' => true,
                    // must be filtered out
                    'foo'                      => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/review/comment/create',
                '{"review_id":"019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8","text":"Спасибо за отзыв!","mark_review_as_processed":true}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::commentList
     */
    public function testCommentList(): void
    {
        $this->quickTest(
            'commentList',
            [
                [
                    'review_id' => '019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8',
                    'filter'    => [
                        'published_from' => '2026-08-01T00:00:00Z',
                        'published_to'   => '2026-08-08T00:00:00Z',
                        'sku'            => '160249683',
                        // must be filtered out
                        'foo'            => 'bar',
                    ],
                    'limit'    => '50',
                    'offset'   => '10',
                    'sort_dir' => 'DESC',
                ],
            ],
            [
                'POST',
                '/v1/review/comment/list',
                '{"limit":50,"offset":10,"sort_dir":"DESC","review_id":"019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8","filter":{"published_from":"2026-08-01T00:00:00Z","published_to":"2026-08-08T00:00:00Z","sku":160249683}}',
            ],
            '{"comments":[],"offset":10}',
            static function (array $result): void {
                self::assertSame(['comments' => [], 'offset' => 10], $result);
            }
        );
    }

    /**
     * @covers ::commentList
     */
    public function testCommentListDefaults(): void
    {
        $this->quickTest(
            'commentList',
            [[]],
            [
                'POST',
                '/v1/review/comment/list',
                '{"limit":100,"offset":0,"sort_dir":"ASC"}',
            ],
            '{"comments":[]}',
            static function (array $result): void {
                self::assertSame(['comments' => []], $result);
            }
        );
    }
}
