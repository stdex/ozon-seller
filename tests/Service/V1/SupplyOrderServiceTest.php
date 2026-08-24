<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\SupplyOrderService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\SupplyOrderService
 */
final class SupplyOrderServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return SupplyOrderService::class;
    }

    /**
     * @covers ::statusCounter
     */
    public function testStatusCounter(): void
    {
        $this->quickTest(
            'statusCounter',
            [],
            ['POST', '/v1/supply-order/status/counter', '{}'],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
            }
        );
    }

    /**
     * @covers ::bundle
     */
    public function testBundle(): void
    {
        $this->quickTest(
            'bundle',
            [
                [
                    'bundle_ids' => ['bundle-1'],
                    'limit'      => '50',
                    'last_id'    => 'prev-id',
                    'query'      => 'заратустра',
                    'is_asc'     => true,
                    'sort_field' => 'SKU',
                    'item_tags_calculation' => [
                        'dropoff_warehouse_id'  => 123,
                        'storage_warehouse_ids' => [456],
                        // must be filtered out
                        'foo'                   => 'bar',
                    ],
                    // must be filtered out
                    'baz' => 'qux',
                ],
            ],
            [
                'POST',
                '/v1/supply-order/bundle',
                '{"limit":50,"bundle_ids":["bundle-1"],"last_id":"prev-id","query":"заратустра","is_asc":true,"sort_field":"SKU","item_tags_calculation":{"dropoff_warehouse_id":"123","storage_warehouse_ids":["456"]}}',
            ],
            '{"items":[],"total_count":0,"has_next":false}',
            static function (array $result): void {
                self::assertSame(['items' => [], 'total_count' => 0, 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::details
     */
    public function testDetails(): void
    {
        $this->quickTest(
            'details',
            [123],
            ['POST', '/v1/supply-order/details', '{"order_id":123}'],
            '{"order_id":123,"state":"ORDER_STATE_DATA_FILLING"}',
            static function (array $result): void {
                self::assertSame(['order_id' => 123, 'state' => 'ORDER_STATE_DATA_FILLING'], $result);
            }
        );
    }

    /**
     * @covers ::cancel
     */
    public function testCancel(): void
    {
        $this->quickTest(
            'cancel',
            [123],
            ['POST', '/v1/supply-order/cancel', '{"order_id":123}'],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::cancelStatus
     */
    public function testCancelStatus(): void
    {
        $this->quickTest(
            'cancelStatus',
            ['op-1'],
            ['POST', '/v1/supply-order/cancel/status', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS","result":{"is_order_cancelled":true}}',
            static function (array $result): void {
                self::assertSame('SUCCESS', $result['status']);
                self::assertTrue($result['result']['is_order_cancelled']);
            }
        );
    }

    /**
     * @covers ::timeslotGet
     */
    public function testTimeslotGet(): void
    {
        $this->quickTest(
            'timeslotGet',
            [123],
            ['POST', '/v1/supply-order/timeslot/get', '{"supply_order_id":123}'],
            '{"timeslots":[],"timezone":[]}',
            static function (array $result): void {
                self::assertSame(['timeslots' => [], 'timezone' => []], $result);
            }
        );
    }

    /**
     * @covers ::timeslotUpdate
     */
    public function testTimeslotUpdate(): void
    {
        $this->quickTest(
            'timeslotUpdate',
            [123, '2026-08-25T10:00:00Z', '2026-08-25T12:00:00Z'],
            [
                'POST',
                '/v1/supply-order/timeslot/update',
                '{"supply_order_id":123,"timeslot":{"from":"2026-08-25T10:00:00Z","to":"2026-08-25T12:00:00Z"}}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::timeslotStatus
     */
    public function testTimeslotStatus(): void
    {
        $this->quickTest(
            'timeslotStatus',
            ['op-1'],
            ['POST', '/v1/supply-order/timeslot/status', '{"operation_id":"op-1"}'],
            '{"status":"STATUS_SUCCESS","errors":[]}',
            static function (array $result): void {
                self::assertSame(['status' => 'STATUS_SUCCESS', 'errors' => []], $result);
            }
        );
    }

    /**
     * @covers ::passCreate
     */
    public function testPassCreate(): void
    {
        $this->quickTest(
            'passCreate',
            [
                123,
                [
                    'driver_name'    => 'Иванов И.И.',
                    'driver_phone'   => '+79001234567',
                    'vehicle_model'  => 'ГАЗель',
                    'vehicle_number' => 'А123БВ777',
                    // must be filtered out
                    'foo'            => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/supply-order/pass/create',
                '{"supply_order_id":123,"vehicle":{"driver_name":"Иванов И.И.","driver_phone":"+79001234567","vehicle_model":"ГАЗель","vehicle_number":"А123БВ777"}}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::passStatus
     */
    public function testPassStatus(): void
    {
        $this->quickTest(
            'passStatus',
            ['op-1'],
            ['POST', '/v1/supply-order/pass/status', '{"operation_id":"op-1"}'],
            '{"result":"Success","errors":[]}',
            static function (array $result): void {
                self::assertSame(['result' => 'Success', 'errors' => []], $result);
            }
        );
    }

    /**
     * @covers ::contentUpdate
     */
    public function testContentUpdate(): void
    {
        $this->quickTest(
            'contentUpdate',
            [
                123,
                456,
                [
                    [
                        'sku'      => '160249683',
                        'quantity' => '10',
                        'quant'    => '1',
                        // must be filtered out
                        'foo'      => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/supply-order/content/update',
                '{"order_id":123,"supply_id":456,"items":[{"sku":160249683,"quantity":10,"quant":1}]}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::contentUpdateStatus
     */
    public function testContentUpdateStatus(): void
    {
        $this->quickTest(
            'contentUpdateStatus',
            ['op-1'],
            ['POST', '/v1/supply-order/content/update/status', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS","new_bundle_id":"bundle-2"}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS', 'new_bundle_id' => 'bundle-2'], $result);
            }
        );
    }

    /**
     * @covers ::contentUpdateValidation
     */
    public function testContentUpdateValidation(): void
    {
        $this->quickTest(
            'contentUpdateValidation',
            [456, 'bundle-2'],
            [
                'POST',
                '/v1/supply-order/content/update/validation',
                '{"supply_id":456,"new_bundle_id":"bundle-2"}',
            ],
            '{"editing_errors":[]}',
            static function (array $result): void {
                self::assertSame(['editing_errors' => []], $result);
            }
        );
    }

    /**
     * @covers ::actSummaryGet
     */
    public function testActSummaryGet(): void
    {
        $this->quickTest(
            'actSummaryGet',
            [123],
            ['POST', '/v1/supply-order/act/summary/get', '{"order_id":123}'],
            '{"supplies_acts":[]}',
            static function (array $result): void {
                self::assertSame(['supplies_acts' => []], $result);
            }
        );
    }

    /**
     * @covers ::actProductGet
     */
    public function testActProductGet(): void
    {
        $this->quickTest(
            'actProductGet',
            [456],
            ['POST', '/v1/supply-order/act/product/get', '{"supply_id":456}'],
            '{"supply_id":456,"supply_acts":[],"skus_defects":[]}',
            static function (array $result): void {
                self::assertSame(456, $result['supply_id']);
            }
        );
    }

    /**
     * @covers ::actAccept
     */
    public function testActAccept(): void
    {
        $this->quickTest(
            'actAccept',
            [789],
            ['POST', '/v1/supply-order/act/accept', '{"act_id":789}'],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::actAcceptStatus
     */
    public function testActAcceptStatus(): void
    {
        $this->quickTest(
            'actAcceptStatus',
            ['op-1'],
            ['POST', '/v1/supply-order/act/accept/status', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS"}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS'], $result);
            }
        );
    }
}
