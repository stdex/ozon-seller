<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\PolygonService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\PolygonService
 */
final class PolygonServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return PolygonService::class;
    }

    /**
     * @covers ::bind
     */
    public function testBind(): void
    {
        $this->quickTest(
            'bind',
            [1234, 123, 456, 30],
            [
                'POST',
                '/v2/polygon/bind',
                '{"polygon_id":1234,"delivery_method_id":123,"warehouse_id":456,"time":30}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }
}
