<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\CargoesService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\CargoesService
 */
final class CargoesServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return CargoesService::class;
    }

    /**
     * The response has a top-level `result` key, so it is returned unwrapped.
     *
     * @covers ::createInfo
     */
    public function testCreateInfo(): void
    {
        $this->quickTest(
            'createInfo',
            ['op-1'],
            ['POST', '/v2/cargoes/create/info', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS","result":{"cargoes":[]}}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS', 'result' => ['cargoes' => []]], $result);
            }
        );
    }

    /**
     * @covers ::get
     */
    public function testGet(): void
    {
        $this->quickTest(
            'get',
            [
                [
                    [
                        'supply_id' => '123',
                        'cargo_ids' => [1, 2],
                        // must be filtered out
                        'foo'       => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v2/cargoes/get',
                '{"supplies":[{"supply_id":123,"cargo_ids":["1","2"]}]}',
            ],
            '{"supplies":[]}',
            static function (array $result): void {
                self::assertSame(['supplies' => []], $result);
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
            [
                [
                    'supply_id'                     => '123',
                    'transport_cargo_deletion_type' => 'UNBIND_CONTAINED_CARGOES',
                    'cargo_ids'                     => [1],
                    'transport_cargo_ids'           => [10],
                    // must be filtered out
                    'foo'                           => 'bar',
                ],
            ],
            [
                'POST',
                '/v2/cargoes/delete',
                '{"supply_id":123,"transport_cargo_deletion_type":"UNBIND_CONTAINED_CARGOES","cargo_ids":["1"],"transport_cargo_ids":["10"]}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::deleteStatus
     */
    public function testDeleteStatus(): void
    {
        $this->quickTest(
            'deleteStatus',
            ['op-1'],
            ['POST', '/v2/cargoes/delete/status', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS"}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS'], $result);
            }
        );
    }
}
