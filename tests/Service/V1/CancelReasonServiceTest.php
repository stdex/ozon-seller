<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\CancelReasonService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\CancelReasonService
 */
final class CancelReasonServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return CancelReasonService::class;
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $this->quickTest(
            'list',
            [],
            ['POST', '/v1/cancel-reason/list', '{}'],
            '{"reasons":[]}',
            static function (array $result): void {
                self::assertSame(['reasons' => []], $result);
            }
        );
    }

    /**
     * @covers ::listByOrder
     */
    public function testListByOrder(): void
    {
        $this->quickTest(
            'listByOrder',
            ['33920474-0032'],
            ['POST', '/v1/cancel-reason/list-by-order', '{"order_number":"33920474-0032"}'],
            '{"reasons":[]}',
            static function (array $result): void {
                self::assertSame(['reasons' => []], $result);
            }
        );
    }

    /**
     * @covers ::listByPosting
     */
    public function testListByPosting(): void
    {
        $this->quickTest(
            'listByPosting',
            ['33920474-0032-1'],
            ['POST', '/v1/cancel-reason/list-by-posting', '{"posting_number":"33920474-0032-1"}'],
            '{"reasons":[]}',
            static function (array $result): void {
                self::assertSame(['reasons' => []], $result);
            }
        );
    }
}
