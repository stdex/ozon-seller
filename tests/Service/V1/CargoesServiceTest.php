<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\CargoesService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\CargoesService
 */
final class CargoesServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return CargoesService::class;
    }

    /**
     * @covers ::create
     */
    public function testCreate(): void
    {
        $this->quickTest(
            'create',
            [
                123,
                [
                    [
                        'key'   => 'box-1',
                        'value' => ['type' => 'BOX', 'items' => []],
                        // must be filtered out
                        'foo'   => 'bar',
                    ],
                ],
                true,
            ],
            [
                'POST',
                '/v1/cargoes/create',
                '{"supply_id":123,"cargoes":[{"key":"box-1","value":{"type":"BOX","items":[]}}],"delete_current_version":true}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
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
            [[123, '456']],
            ['POST', '/v1/cargoes/get', '{"supply_ids":["123","456"]}'],
            '{"supply":[]}',
            static function (array $result): void {
                self::assertSame(['supply' => []], $result);
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
            [123, [1, 2]],
            ['POST', '/v1/cargoes/delete', '{"supply_id":123,"cargo_ids":["1","2"]}'],
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
            ['POST', '/v1/cargoes/delete/status', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS"}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS'], $result);
            }
        );
    }

    /**
     * @covers ::rulesGet
     */
    public function testRulesGet(): void
    {
        $this->quickTest(
            'rulesGet',
            [[123]],
            ['POST', '/v1/cargoes/rules/get', '{"supply_ids":["123"]}'],
            '{"supply_check_lists":[]}',
            static function (array $result): void {
                self::assertSame(['supply_check_lists' => []], $result);
            }
        );
    }

    /**
     * @covers ::suppliesGet
     */
    public function testSuppliesGet(): void
    {
        $this->quickTest(
            'suppliesGet',
            [[123]],
            ['POST', '/v1/cargoes/supplies/get', '{"supply_ids":["123"]}'],
            '{"supplies_cargoes":[],"not_found_supply_ids":[]}',
            static function (array $result): void {
                self::assertSame(['supplies_cargoes' => [], 'not_found_supply_ids' => []], $result);
            }
        );
    }

    /**
     * @covers ::transportCreate
     */
    public function testTransportCreate(): void
    {
        $this->quickTest(
            'transportCreate',
            [
                123,
                [
                    [
                        'type'  => 'PALLET',
                        'count' => '2',
                        // must be filtered out
                        'foo'   => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/cargoes/transport/create',
                '{"supply_id":123,"transport_cargoes":[{"type":"PALLET","count":2}]}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::transportCreateStatus
     */
    public function testTransportCreateStatus(): void
    {
        $this->quickTest(
            'transportCreateStatus',
            ['op-1'],
            ['POST', '/v1/cargoes/transport/create/status', '{"operation_id":"op-1"}'],
            '{"status":"IN_PROGRESS"}',
            static function (array $result): void {
                self::assertSame(['status' => 'IN_PROGRESS'], $result);
            }
        );
    }

    /**
     * @covers ::transportActivate
     */
    public function testTransportActivate(): void
    {
        $this->quickTest(
            'transportActivate',
            [123, true],
            [
                'POST',
                '/v1/cargoes/transport/activate',
                '{"supply_id":123,"is_transport":true}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::transportActivateStatus
     */
    public function testTransportActivateStatus(): void
    {
        $this->quickTest(
            'transportActivateStatus',
            ['op-1'],
            ['POST', '/v1/cargoes/transport/activate/status', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS","error_reasons":[]}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS', 'error_reasons' => []], $result);
            }
        );
    }

    /**
     * @covers ::transportBind
     */
    public function testTransportBind(): void
    {
        $this->quickTest(
            'transportBind',
            [
                123,
                [
                    [
                        'transport_cargo_id' => '10',
                        'cargo_ids'          => [1, 2],
                        // must be filtered out
                        'foo'                => 'bar',
                    ],
                ],
                [3],
            ],
            [
                'POST',
                '/v1/cargoes/transport/bind',
                '{"supply_id":123,"transport_cargo_bind":[{"transport_cargo_id":10,"cargo_ids":["1","2"]}],"cargoes_unbind_transport_cargoes":["3"]}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::transportBind
     */
    public function testTransportBindOnlySupply(): void
    {
        $this->quickTest(
            'transportBind',
            [123],
            ['POST', '/v1/cargoes/transport/bind', '{"supply_id":123}'],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::transportBindStatus
     */
    public function testTransportBindStatus(): void
    {
        $this->quickTest(
            'transportBindStatus',
            ['op-1'],
            ['POST', '/v1/cargoes/transport/bind/status', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS"}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS'], $result);
            }
        );
    }

    /**
     * @covers ::labelTransportCreate
     */
    public function testLabelTransportCreate(): void
    {
        $this->quickTest(
            'labelTransportCreate',
            [123, [10]],
            [
                'POST',
                '/v1/cargoes/label/transport/create',
                '{"supply_id":123,"transport_cargo_ids":["10"]}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::labelTransportStatus
     */
    public function testLabelTransportStatus(): void
    {
        $this->quickTest(
            'labelTransportStatus',
            ['op-1'],
            ['POST', '/v1/cargoes/label/transport/status', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS","result":{"file_url":"https://cdn.ozone.ru/label.pdf"}}',
            static function (array $result): void {
                self::assertSame('SUCCESS', $result['status']);
            }
        );
    }

    /**
     * @covers ::labelTransportByOrderCreate
     */
    public function testLabelTransportByOrderCreate(): void
    {
        $this->quickTest(
            'labelTransportByOrderCreate',
            [456],
            [
                'POST',
                '/v1/cargoes/label/transport-by-order/create',
                '{"order_id":456}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::labelTransportByOrderStatus
     */
    public function testLabelTransportByOrderStatus(): void
    {
        $this->quickTest(
            'labelTransportByOrderStatus',
            ['op-1'],
            ['POST', '/v1/cargoes/label/transport-by-order/status', '{"operation_id":"op-1"}'],
            '{"status":"FAILED","error_reasons":["ORDER_NOT_FOUND"]}',
            static function (array $result): void {
                self::assertSame(['status' => 'FAILED', 'error_reasons' => ['ORDER_NOT_FOUND']], $result);
            }
        );
    }

    /**
     * @covers ::labelCreate
     */
    public function testLabelCreate(): void
    {
        $this->quickTest(
            'labelCreate',
            [
                123,
                [
                    [
                        'cargo_id' => '10',
                        // must be filtered out
                        'foo'      => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/cargoes-label/create',
                '{"supply_id":123,"cargoes":[{"cargo_id":10}]}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::labelGet
     */
    public function testLabelGet(): void
    {
        $this->quickTest(
            'labelGet',
            ['op-1'],
            ['POST', '/v1/cargoes-label/get', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS","result":{"file_guid":"guid-1","file_url":"https://cdn.ozone.ru/label.pdf"}}',
            static function (array $result): void {
                self::assertSame('guid-1', $result['result']['file_guid']);
            }
        );
    }

    /**
     * @covers ::labelFile
     */
    public function testLabelFile(): void
    {
        $this->quickTest(
            'labelFile',
            ['guid-1'],
            ['GET', '/v1/cargoes-label/file/guid-1', null],
            '%PDF-1.4',
            static function ($result): void {
                self::assertSame('%PDF-1.4', $result);
            }
        );
    }
}
