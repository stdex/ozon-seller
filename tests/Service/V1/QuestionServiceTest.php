<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\QuestionService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\QuestionService
 */
final class QuestionServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return QuestionService::class;
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $json = <<<JSON
{
  "questions": [
    {
      "id": "019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8",
      "sku": 160249683,
      "text": "Есть ли твёрдый переплёт?",
      "author_name": "Иван",
      "answers_count": 0,
      "status": "NEW"
    }
  ],
  "last_id": "next-id",
  "has_next": false
}
JSON;

        $this->quickTest(
            'list',
            [
                [
                    'filter' => [
                        'date_from' => '2026-08-01T00:00:00Z',
                        'date_to'   => '2026-08-08T00:00:00Z',
                        'status'    => 'NEW',
                        // must be filtered out
                        'foo'       => 'bar',
                    ],
                    'last_id'  => 'prev-id',
                    'limit'    => '50',
                    'sort_dir' => 'DESC',
                ],
            ],
            [
                'POST',
                '/v1/question/list',
                '{"limit":50,"sort_dir":"DESC","filter":{"date_from":"2026-08-01T00:00:00Z","date_to":"2026-08-08T00:00:00Z","status":"NEW"},"last_id":"prev-id"}',
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
                '/v1/question/list',
                '{"limit":100,"sort_dir":"ASC"}',
            ],
            '{"questions":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['questions' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::info
     */
    public function testInfo(): void
    {
        $json = '{"id":"019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8","sku":160249683,"status":"VIEWED"}';

        $this->quickTest(
            'info',
            ['019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8'],
            [
                'POST',
                '/v1/question/info',
                '{"question_id":"019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8"}',
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
        $json = '{"all":10,"new":3,"viewed":4,"processed":2,"unprocessed":1}';

        $this->quickTest(
            'count',
            [],
            ['POST', '/v1/question/count', '{}'],
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
                '/v1/question/change-status',
                '{"question_ids":["019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8"],"status":"PROCESSED"}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::topSku
     */
    public function testTopSku(): void
    {
        $json = '{"sku":["160249683","160249684"]}';

        $this->quickTest(
            'topSku',
            [20],
            ['POST', '/v1/question/top-sku', '{"limit":20}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::answerCreate
     */
    public function testAnswerCreate(): void
    {
        $json = '{"answer_id":"019c9c7a-b1a8-7cd6-a1a4-1b0b2a2b1c1d"}';

        $this->quickTest(
            'answerCreate',
            ['019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8', 160249683, 'Да, твёрдый.'],
            [
                'POST',
                '/v1/question/answer/create',
                '{"question_id":"019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8","sku":160249683,"text":"Да, твёрдый."}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::answerDelete
     */
    public function testAnswerDelete(): void
    {
        $this->quickTest(
            'answerDelete',
            ['019c9c7a-b1a8-7cd6-a1a4-1b0b2a2b1c1d', 160249683],
            [
                'POST',
                '/v1/question/answer/delete',
                '{"answer_id":"019c9c7a-b1a8-7cd6-a1a4-1b0b2a2b1c1d","sku":160249683}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::answerList
     */
    public function testAnswerList(): void
    {
        $this->quickTest(
            'answerList',
            ['019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8', 160249683],
            [
                'POST',
                '/v1/question/answer/list',
                '{"question_id":"019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8","sku":160249683}',
            ],
            '{"answers":[],"last_id":""}',
            static function (array $result): void {
                self::assertSame(['answers' => [], 'last_id' => ''], $result);
            }
        );
    }

    /**
     * @covers ::answerList
     */
    public function testAnswerListWithLastId(): void
    {
        $this->quickTest(
            'answerList',
            ['019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8', 160249683, 'prev-id'],
            [
                'POST',
                '/v1/question/answer/list',
                '{"question_id":"019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8","sku":160249683,"last_id":"prev-id"}',
            ],
            '{"answers":[]}',
            static function (array $result): void {
                self::assertSame(['answers' => []], $result);
            }
        );
    }
}
