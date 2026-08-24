<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1\Posting;

use Gam6itko\OzonSeller\Service\V1\Posting\FboService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\Posting\FboService
 */
final class FboServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return FboService::class;
    }

    /**
     * @covers ::cancelReasonList
     */
    public function testCancelReasonList(): void
    {
        $this->quickTest(
            'cancelReasonList',
            [],
            ['POST', '/v1/posting/fbo/cancel-reason/list', '{}'],
            '{"reasons":[]}',
            static function (array $result): void {
                self::assertSame(['reasons' => []], $result);
            }
        );
    }
}
