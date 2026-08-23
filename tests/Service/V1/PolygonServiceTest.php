<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\PolygonService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\PolygonService
 */
final class PolygonServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return PolygonService::class;
    }

    /**
     * @covers ::create
     */
    public function testCreate(): void
    {
        $this->quickTest(
            'create',
            ['{"coordinates":[[[55.7,37.6]]]}'],
            [
                'POST',
                '/v1/polygon/create',
                '{"coordinates":"{\"coordinates\":[[[55.7,37.6]]]}"}',
            ],
            '{"polygon_id":1234}',
            static function (array $result): void {
                self::assertSame(['polygon_id' => 1234], $result);
            }
        );
    }

    /**
     * @covers ::bind
     */
    public function testBind(): void
    {
        $this->quickTest(
            'bind',
            [
                [
                    'delivery_method_id' => '123',
                    'warehouse_location' => [
                        'lat' => '55.7',
                        'lon' => '37.6',
                        // must be filtered out
                        'foo' => 'bar',
                    ],
                    'polygons' => [
                        [
                            'polygon_id' => '1234',
                            'time'       => '30',
                            // must be filtered out
                            'foo'        => 'bar',
                        ],
                    ],
                    // must be filtered out
                    'bar' => 'baz',
                ],
            ],
            [
                'POST',
                '/v1/polygon/bind',
                '{"delivery_method_id":123,"warehouse_location":{"lat":"55.7","lon":"37.6"},"polygons":[{"polygon_id":1234,"time":30}]}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
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
            [123, 456],
            [
                'POST',
                '/v1/polygon/list',
                '{"delivery_method_id":123,"warehouse_id":456}',
            ],
            '{"polygons":[]}',
            static function (array $result): void {
                self::assertSame(['polygons' => []], $result);
            }
        );
    }

    /**
     * @covers ::delete
     */
    public function testDelete(): void
    {
        $this->quickTest(
            'delete',
            [1234, 123, 456],
            [
                'POST',
                '/v1/polygon/delete',
                '{"polygon_id":1234,"delivery_method_id":123,"warehouse_id":456}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::timeCoordinatesUpdate
     */
    public function testTimeCoordinatesUpdate(): void
    {
        $this->quickTest(
            'timeCoordinatesUpdate',
            [1234, 123, 456, '{"coordinates":[]}'],
            [
                'POST',
                '/v1/polygon/time/coordinates/update',
                '{"polygon_id":1234,"delivery_method_id":123,"warehouse_id":456,"coordinates":"{\"coordinates\":[]}"}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::timeSet
     */
    public function testTimeSet(): void
    {
        $this->quickTest(
            'timeSet',
            [1234, 123, 456, 30, 60],
            [
                'POST',
                '/v1/polygon/time/set',
                '{"polygon_id":1234,"delivery_method_id":123,"warehouse_id":456,"current_time":30,"new_time":60}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }
}
