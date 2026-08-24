<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2\Posting;

use Gam6itko\OzonSeller\Service\V2\Posting\DigitalService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\Posting\DigitalService
 */
final class DigitalServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return DigitalService::class;
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
                        'order_numbers'   => ['33920474-0032'],
                        'posting_numbers' => ['33920474-0032-1'],
                        // must be filtered out
                        'foo'             => 'bar',
                    ],
                    'with'     => ['analytics_data' => true, 'foo' => 'bar'],
                    'cursor'   => 'prev-cursor',
                    'limit'    => '50',
                    'sort_dir' => 'DESC',
                ],
            ],
            [
                'POST',
                '/v2/posting/digital/list',
                '{"limit":50,"sort_dir":"DESC","filter":{"since":"2026-08-01T00:00:00Z","to":"2026-08-08T00:00:00Z","order_numbers":["33920474-0032"],"posting_numbers":["33920474-0032-1"]},"with":{"analytics_data":true},"cursor":"prev-cursor"}',
            ],
            '{"postings":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['postings' => [], 'has_next' => false], $result);
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
            ['POST', '/v2/posting/digital/list', '{"limit":100,"sort_dir":"ASC"}'],
            '{"postings":[]}',
            static function (array $result): void {
                self::assertSame(['postings' => []], $result);
            }
        );
    }
}
