<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1\Posting;

use Gam6itko\OzonSeller\Service\V1\Posting\FbpService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\Posting\FbpService
 */
final class FbpServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return FbpService::class;
    }

    /**
     * @covers ::get
     */
    public function testGet(): void
    {
        $json = '{"posting":{"posting_number":"33920474-0032-1","order_id":354679434,"status":1}}';

        $this->quickTest(
            'get',
            ['33920474-0032-1'],
            ['POST', '/v1/posting/fbp/get', '{"posting_number":"33920474-0032-1"}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
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
                        'since'           => '2026-08-01T00:00:00Z',
                        'to'              => '2026-08-08T00:00:00Z',
                        'statuses'        => ['delivering'],
                        'posting_numbers' => ['33920474-0032-1'],
                        'name'            => 'Заратустра',
                        'offer_id'        => '9789785079999',
                        // must be filtered out
                        'foo'             => 'bar',
                    ],
                    'cursor'   => 'prev-cursor',
                    'limit'    => '50',
                    'sort_by'  => 'created_at',
                    'sort_dir' => 'DESC',
                ],
            ],
            [
                'POST',
                '/v1/posting/fbp/list',
                '{"limit":50,"sort_dir":"DESC","filter":{"since":"2026-08-01T00:00:00Z","to":"2026-08-08T00:00:00Z","statuses":["delivering"],"posting_numbers":["33920474-0032-1"],"name":"Заратустра","offer_id":"9789785079999"},"cursor":"prev-cursor","sort_by":"created_at"}',
            ],
            '{"postings":[],"cursor":""}',
            static function (array $result): void {
                self::assertSame(['postings' => [], 'cursor' => ''], $result);
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
            ['POST', '/v1/posting/fbp/list', '{"limit":10,"sort_dir":"ASC"}'],
            '{"postings":[]}',
            static function (array $result): void {
                self::assertSame(['postings' => []], $result);
            }
        );
    }
}
