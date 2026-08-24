<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\WarehouseService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\WarehouseService
 */
final class WarehouseServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return WarehouseService::class;
    }

    /**
     * @covers ::operationStatus
     */
    public function testOperationStatus(): void
    {
        $this->quickTest(
            'operationStatus',
            ['op-1'],
            ['POST', '/v1/warehouse/operation/status', '{"operation_id":"op-1"}'],
            '{"status":"SUCCESS","type":"CREATE_FBS_WAREHOUSE","result":{"entity_id":123}}',
            static function (array $result): void {
                self::assertSame('SUCCESS', $result['status']);
                self::assertSame(123, $result['result']['entity_id']);
            }
        );
    }

    /**
     * @covers ::archive
     */
    public function testArchive(): void
    {
        $this->quickTest(
            'archive',
            [123, 'closed', 456],
            [
                'POST',
                '/v1/warehouse/archive',
                '{"warehouse_id":123,"reason":"closed","return_point_id":456}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::unarchive
     */
    public function testUnarchive(): void
    {
        $this->quickTest(
            'unarchive',
            [123],
            ['POST', '/v1/warehouse/unarchive', '{"warehouse_id":123}'],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::invalidProductsGet
     */
    public function testInvalidProductsGet(): void
    {
        $this->quickTest(
            'invalidProductsGet',
            [123, 10],
            [
                'POST',
                '/v1/warehouse/invalid-products/get',
                '{"warehouse_id":123,"last_id":10}',
            ],
            '{"validation_results":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['validation_results' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::warehousesWithInvalidProducts
     */
    public function testWarehousesWithInvalidProducts(): void
    {
        $this->quickTest(
            'warehousesWithInvalidProducts',
            [],
            ['POST', '/v1/warehouse/warehouses-with-invalid-products', '{}'],
            '{"warehouse_ids":["123"]}',
            static function (array $result): void {
                self::assertSame(['warehouse_ids' => ['123']], $result);
            }
        );
    }

    /**
     * @covers ::ozonList
     */
    public function testOzonList(): void
    {
        $this->quickTest(
            'ozonList',
            [['WAREHOUSE_TYPE_CROSSDOCK']],
            [
                'POST',
                '/v1/warehouse/ozon/list',
                '{"warehouse_types":["WAREHOUSE_TYPE_CROSSDOCK"]}',
            ],
            '{"warehouses":[]}',
            static function (array $result): void {
                self::assertSame(['warehouses' => []], $result);
            }
        );
    }

    /**
     * @covers ::ozonList
     */
    public function testOzonListNoFilter(): void
    {
        $this->quickTest(
            'ozonList',
            [],
            ['POST', '/v1/warehouse/ozon/list', '{}'],
            '{"warehouses":[]}',
            static function (array $result): void {
                self::assertSame(['warehouses' => []], $result);
            }
        );
    }

    /**
     * @covers ::fboList
     */
    public function testFboList(): void
    {
        $this->quickTest(
            'fboList',
            ['Хоругвино', ['CREATE_TYPE_DIRECT']],
            [
                'POST',
                '/v1/warehouse/fbo/list',
                '{"search":"Хоругвино","filter_by_supply_type":["CREATE_TYPE_DIRECT"]}',
            ],
            '{"search":[]}',
            static function (array $result): void {
                self::assertSame(['search' => []], $result);
            }
        );
    }

    /**
     * @covers ::fboSellerList
     */
    public function testFboSellerList(): void
    {
        $this->quickTest(
            'fboSellerList',
            [],
            ['POST', '/v1/warehouse/fbo/seller/list', '{}'],
            '{"warehouses":[]}',
            static function (array $result): void {
                self::assertSame(['warehouses' => []], $result);
            }
        );
    }

    /**
     * @covers ::rfbsPause
     */
    public function testRfbsPause(): void
    {
        $this->quickTest(
            'rfbsPause',
            [123],
            ['POST', '/v1/warehouse/rfbs/pause', '{"warehouse_id":123}'],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::rfbsUnpause
     */
    public function testRfbsUnpause(): void
    {
        $this->quickTest(
            'rfbsUnpause',
            [123],
            ['POST', '/v1/warehouse/rfbs/unpause', '{"warehouse_id":123}'],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::fbsCreate
     */
    public function testFbsCreate(): void
    {
        $this->quickTest(
            'fbsCreate',
            [
                [
                    'name'                => 'Основной склад',
                    'phone'               => '+79001234567',
                    'address_coordinates' => [
                        'latitude'  => '55.7',
                        'longitude' => '37.6',
                        // must be filtered out
                        'foo'       => 'bar',
                    ],
                    'first_mile_type'   => 'DROP_OFF',
                    'cut_in_time'       => '18',
                    'timeslot_id'       => '1',
                    'is_kgt'            => false,
                    'drop_off_point_id' => '456',
                    'working_days'      => ['MONDAY', 'TUESDAY'],
                    'options'           => [
                        'comment'          => 'звонить заранее',
                        'is_auto_assembly' => true,
                        // must be filtered out
                        'foo'              => 'bar',
                    ],
                    // must be filtered out
                    'baz' => 'qux',
                ],
            ],
            [
                'POST',
                '/v1/warehouse/fbs/create',
                '{"name":"Основной склад","phone":"+79001234567","address_coordinates":{"latitude":55.7,"longitude":37.6},"first_mile_type":"DROP_OFF","cut_in_time":18,"timeslot_id":1,"is_kgt":false,"drop_off_point_id":456,"working_days":["MONDAY","TUESDAY"],"options":{"comment":"звонить заранее","is_auto_assembly":true}}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::fbsUpdate
     */
    public function testFbsUpdate(): void
    {
        $this->quickTest(
            'fbsUpdate',
            [
                [
                    'warehouse_id'        => '123',
                    'address_coordinates' => ['latitude' => 55.7, 'longitude' => 37.6],
                    'name'                => 'Новое имя',
                    'phone'               => '+79001234567',
                ],
            ],
            [
                'POST',
                '/v1/warehouse/fbs/update',
                '{"warehouse_id":123,"address_coordinates":{"latitude":55.7,"longitude":37.6},"name":"Новое имя","phone":"+79001234567"}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::fbsFirstMileUpdate
     */
    public function testFbsFirstMileUpdate(): void
    {
        $this->quickTest(
            'fbsFirstMileUpdate',
            [
                [
                    'warehouse_id'      => 123,
                    'first_mile_type'   => 'PICK_UP',
                    'cut_in_time'       => 18,
                    'timeslot_id'       => 1,
                    'drop_off_point_id' => 456,
                    // must be filtered out
                    'foo'               => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/warehouse/fbs/first-mile/update',
                '{"warehouse_id":123,"first_mile_type":"PICK_UP","cut_in_time":18,"timeslot_id":1,"drop_off_point_id":456}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::fbsCreateDropOffList
     */
    public function testFbsCreateDropOffList(): void
    {
        $this->quickTest(
            'fbsCreateDropOffList',
            [
                [
                    'country_code' => 'RU',
                    'is_kgt'       => false,
                    'coordinates'  => ['latitude' => 55.7, 'longitude' => 37.6],
                    'search'       => [
                        'address' => 'Москва',
                        'types'   => ['PVZ'],
                        // must be filtered out
                        'foo'     => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/warehouse/fbs/create/drop-off/list',
                '{"country_code":"RU","is_kgt":false,"coordinates":{"latitude":55.7,"longitude":37.6},"search":{"address":"Москва","types":["PVZ"]}}',
            ],
            '{"points":[]}',
            static function (array $result): void {
                self::assertSame(['points' => []], $result);
            }
        );
    }

    /**
     * @covers ::fbsUpdateDropOffList
     */
    public function testFbsUpdateDropOffList(): void
    {
        $this->quickTest(
            'fbsUpdateDropOffList',
            [123, ['address' => 'Москва']],
            [
                'POST',
                '/v1/warehouse/fbs/update/drop-off/list',
                '{"warehouse_id":123,"search":{"address":"Москва"}}',
            ],
            '{"points":[]}',
            static function (array $result): void {
                self::assertSame(['points' => []], $result);
            }
        );
    }

    /**
     * @covers ::fbsCreateDropOffTimeslotList
     */
    public function testFbsCreateDropOffTimeslotList(): void
    {
        $this->quickTest(
            'fbsCreateDropOffTimeslotList',
            [456],
            [
                'POST',
                '/v1/warehouse/fbs/create/drop-off/timeslot/list',
                '{"drop_off_point_id":456}',
            ],
            '{"timeslots":[]}',
            static function (array $result): void {
                self::assertSame(['timeslots' => []], $result);
            }
        );
    }

    /**
     * @covers ::fbsUpdateDropOffTimeslotList
     */
    public function testFbsUpdateDropOffTimeslotList(): void
    {
        $this->quickTest(
            'fbsUpdateDropOffTimeslotList',
            [123, 456],
            [
                'POST',
                '/v1/warehouse/fbs/update/drop-off/timeslot/list',
                '{"warehouse_id":123,"drop_off_point_id":456}',
            ],
            '{"timeslots":[]}',
            static function (array $result): void {
                self::assertSame(['timeslots' => []], $result);
            }
        );
    }

    /**
     * @covers ::fbsCreatePickUpTimeslotList
     */
    public function testFbsCreatePickUpTimeslotList(): void
    {
        $this->quickTest(
            'fbsCreatePickUpTimeslotList',
            [['latitude' => '55.7', 'longitude' => '37.6'], true],
            [
                'POST',
                '/v1/warehouse/fbs/create/pick-up/timeslot/list',
                '{"address_coordinates":{"latitude":55.7,"longitude":37.6},"is_kgt":true}',
            ],
            '{"timeslots":[],"is_pickup_supported":true}',
            static function (array $result): void {
                self::assertSame(['timeslots' => [], 'is_pickup_supported' => true], $result);
            }
        );
    }

    /**
     * @covers ::fbsUpdatePickUpTimeslotList
     */
    public function testFbsUpdatePickUpTimeslotList(): void
    {
        $this->quickTest(
            'fbsUpdatePickUpTimeslotList',
            [123],
            [
                'POST',
                '/v1/warehouse/fbs/update/pick-up/timeslot/list',
                '{"warehouse_id":123}',
            ],
            '{"timeslots":[]}',
            static function (array $result): void {
                self::assertSame(['timeslots' => []], $result);
            }
        );
    }

    /**
     * @covers ::fbsCreateReturnPointList
     */
    public function testFbsCreateReturnPointList(): void
    {
        $this->quickTest(
            'fbsCreateReturnPointList',
            [
                [
                    'coordinates'               => ['latitude' => 55.7, 'longitude' => 37.6],
                    'country_code'              => 'RU',
                    'limit'                     => '20',
                    'last_id'                   => '5',
                    'selected_dropoff_point_id' => '456',
                ],
            ],
            [
                'POST',
                '/v1/warehouse/fbs/create/return-point/list',
                '{"limit":20,"coordinates":{"latitude":55.7,"longitude":37.6},"country_code":"RU","last_id":5,"selected_dropoff_point_id":456}',
            ],
            '{"points":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['points' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::fbsUpdateReturnPointList
     */
    public function testFbsUpdateReturnPointList(): void
    {
        $this->quickTest(
            'fbsUpdateReturnPointList',
            [['warehouse_id' => 123]],
            [
                'POST',
                '/v1/warehouse/fbs/update/return-point/list',
                '{"limit":100,"warehouse_id":123}',
            ],
            '{"points":[]}',
            static function (array $result): void {
                self::assertSame(['points' => []], $result);
            }
        );
    }

    /**
     * @covers ::fbsReturnMileInfo
     */
    public function testFbsReturnMileInfo(): void
    {
        $this->quickTest(
            'fbsReturnMileInfo',
            [[123, '456']],
            [
                'POST',
                '/v1/warehouse/fbs/return-mile/info',
                '{"warehouse_ids":["123","456"]}',
            ],
            '{"return_mile_settings":[]}',
            static function (array $result): void {
                self::assertSame(['return_mile_settings' => []], $result);
            }
        );
    }

    /**
     * @covers ::fbsReturnMileCheck
     */
    public function testFbsReturnMileCheck(): void
    {
        $this->quickTest(
            'fbsReturnMileCheck',
            [
                [
                    'country_code'    => 'RU',
                    'first_mile_type' => 'DROP_OFF',
                    'is_kgt'          => false,
                    'warehouse_id'    => '123',
                ],
            ],
            [
                'POST',
                '/v1/warehouse/fbs/return-mile/check',
                '{"country_code":"RU","first_mile_type":"DROP_OFF","is_kgt":false,"warehouse_id":123}',
            ],
            '{"should_set_return_mile":true}',
            static function (array $result): void {
                self::assertSame(['should_set_return_mile' => true], $result);
            }
        );
    }

    /**
     * @covers ::fbsPickupCourierCreate
     */
    public function testFbsPickupCourierCreate(): void
    {
        $this->quickTest(
            'fbsPickupCourierCreate',
            [123],
            ['POST', '/v1/warehouse/fbs/pickup/courier/create', '{"warehouse_id":123}'],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::fbsPickupCourierCancel
     */
    public function testFbsPickupCourierCancel(): void
    {
        $this->quickTest(
            'fbsPickupCourierCancel',
            [123],
            ['POST', '/v1/warehouse/fbs/pickup/courier/cancel', '{"warehouse_id":123}'],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::fbsPickupHistoryList
     */
    public function testFbsPickupHistoryList(): void
    {
        $this->quickTest(
            'fbsPickupHistoryList',
            [
                [
                    'filter' => [
                        'planned_date' => '2026-08-25',
                        'warehouse_id' => ['123'],
                        'was_planned'  => true,
                        // must be filtered out
                        'foo'          => 'bar',
                    ],
                    'cursor' => 'prev-cursor',
                    'limit'  => '50',
                ],
            ],
            [
                'POST',
                '/v1/warehouse/fbs/pickup/history/list',
                '{"limit":50,"filter":{"planned_date":"2026-08-25","warehouse_id":[123],"was_planned":true},"cursor":"prev-cursor"}',
            ],
            '{"result":{"history":[],"cursor":""}}'
        );
    }

    /**
     * @covers ::fbsPickupPlanningList
     */
    public function testFbsPickupPlanningList(): void
    {
        $this->quickTest(
            'fbsPickupPlanningList',
            [],
            ['POST', '/v1/warehouse/fbs/pickup/planning/list', '{}'],
            '{"result":{"warehouses":[]}}'
        );
    }

    /**
     * @covers ::erfbsAggregatorCreate
     */
    public function testErfbsAggregatorCreate(): void
    {
        $this->quickTest(
            'erfbsAggregatorCreate',
            [
                [
                    'name'                => 'rFBS склад',
                    'phone'               => '+79001234567',
                    'address_coordinates' => ['latitude' => 55.7, 'longitude' => 37.6],
                    'delivery_method'     => ['name' => 'Курьер', 'cut_in' => 18],
                    'timetable_warehouse' => ['working_days' => []],
                    'is_auto_assembly'    => true,
                    'min_order_value'     => '1000',
                    // must be filtered out
                    'foo'                 => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/warehouse/erfbs/aggregator/create',
                '{"name":"rFBS склад","phone":"+79001234567","address_coordinates":{"latitude":55.7,"longitude":37.6},"delivery_method":{"name":"Курьер","cut_in":18},"timetable_warehouse":{"working_days":[]},"is_auto_assembly":true,"min_order_value":1000}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::erfbsNonIntegratedCreate
     */
    public function testErfbsNonIntegratedCreate(): void
    {
        $this->quickTest(
            'erfbsNonIntegratedCreate',
            [
                [
                    'name'                => 'rFBS склад',
                    'phone'               => '+79001234567',
                    'address_coordinates' => ['latitude' => 55.7, 'longitude' => 37.6],
                    'delivery_method'     => ['name' => 'Своя доставка'],
                    'timetable_warehouse' => ['working_days' => []],
                ],
            ],
            [
                'POST',
                '/v1/warehouse/erfbs/non-integrated/create',
                '{"name":"rFBS склад","phone":"+79001234567","address_coordinates":{"latitude":55.7,"longitude":37.6},"delivery_method":{"name":"Своя доставка"},"timetable_warehouse":{"working_days":[]}}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::erfbsUpdate
     */
    public function testErfbsUpdate(): void
    {
        $this->quickTest(
            'erfbsUpdate',
            [
                [
                    'warehouse_id'     => '123',
                    'name'             => 'Новое имя',
                    'is_auto_assembly' => false,
                ],
            ],
            [
                'POST',
                '/v1/warehouse/erfbs/update',
                '{"warehouse_id":123,"name":"Новое имя","is_auto_assembly":false}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::erfbsAggregatorDeliveryMethodUpdate
     */
    public function testErfbsAggregatorDeliveryMethodUpdate(): void
    {
        $this->quickTest(
            'erfbsAggregatorDeliveryMethodUpdate',
            [
                [
                    'warehouse_id'       => 123,
                    'delivery_method_id' => 456,
                    'name'               => 'Курьер',
                    'courier_phones'     => ['+79001234567'],
                    'cut_in'             => '18',
                    'deliver_to_pvz'     => true,
                    'delivery_costs'     => ['seller_payment' => 100],
                    // must be filtered out
                    'foo'                => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/warehouse/erfbs/aggregator/delivery-method/update',
                '{"warehouse_id":123,"delivery_method_id":456,"name":"Курьер","courier_phones":["+79001234567"],"cut_in":18,"deliver_to_pvz":true,"delivery_costs":{"seller_payment":100}}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }

    /**
     * @covers ::erfbsNonIntegratedDeliveryMethodUpdate
     */
    public function testErfbsNonIntegratedDeliveryMethodUpdate(): void
    {
        $this->quickTest(
            'erfbsNonIntegratedDeliveryMethodUpdate',
            [
                [
                    'warehouse_id'       => 123,
                    'delivery_method_id' => 456,
                    'name'               => 'Своя доставка',
                    'cut_in'             => 18,
                    'courier_cutoff'     => 12,
                    'return_settings'    => ['return_method' => 'COURIER'],
                ],
            ],
            [
                'POST',
                '/v1/warehouse/erfbs/non-integrated/delivery-method/update',
                '{"warehouse_id":123,"delivery_method_id":456,"name":"Своя доставка","cut_in":18,"courier_cutoff":12,"return_settings":{"return_method":"COURIER"}}',
            ],
            '{"operation_id":"op-1"}',
            static function (array $result): void {
                self::assertSame(['operation_id' => 'op-1'], $result);
            }
        );
    }
}
