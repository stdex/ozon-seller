<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\DeliveryService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\DeliveryService
 */
final class DeliveryServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return DeliveryService::class;
    }

    /**
     * @covers ::check
     */
    public function testCheck(): void
    {
        $this->quickTest(
            'check',
            ['+79001234567'],
            ['POST', '/v1/delivery/check', '{"client_phone":"+79001234567"}'],
            '{"is_possible":true}',
            static function (array $result): void {
                self::assertSame(['is_possible' => true], $result);
            }
        );
    }

    /**
     * @covers ::map
     */
    public function testMap(): void
    {
        $this->quickTest(
            'map',
            [
                [
                    'viewport' => [
                        'left_bottom' => ['lat' => 55.5, 'lon' => 37.3],
                        'right_top'   => ['lat' => 55.9, 'lon' => 37.9],
                        // must be filtered out
                        'foo'         => 'bar',
                    ],
                    'zoom' => 12,
                    // must be filtered out
                    'baz'  => 'qux',
                ],
            ],
            [
                'POST',
                '/v1/delivery/map',
                '{"viewport":{"left_bottom":{"lat":55.5,"lon":37.3},"right_top":{"lat":55.9,"lon":37.9}},"zoom":12}',
            ],
            '{"clusters":[]}',
            static function (array $result): void {
                self::assertSame(['clusters' => []], $result);
            }
        );
    }

    /**
     * @covers ::map
     */
    public function testMapEmpty(): void
    {
        $this->quickTest(
            'map',
            [],
            ['POST', '/v1/delivery/map', '{}'],
            '{"clusters":[]}',
            static function (array $result): void {
                self::assertSame(['clusters' => []], $result);
            }
        );
    }

    /**
     * @covers ::pointInfo
     */
    public function testPointInfo(): void
    {
        $this->quickTest(
            'pointInfo',
            [[123, '456']],
            ['POST', '/v1/delivery/point/info', '{"map_point_ids":["123","456"]}'],
            '{"points":[]}',
            static function (array $result): void {
                self::assertSame(['points' => []], $result);
            }
        );
    }

    /**
     * @covers ::pointList
     */
    public function testPointList(): void
    {
        $this->quickTest(
            'pointList',
            [],
            ['POST', '/v1/delivery/point/list', '{}'],
            '{"points":[]}',
            static function (array $result): void {
                self::assertSame(['points' => []], $result);
            }
        );
    }
}
