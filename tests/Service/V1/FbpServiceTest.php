<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\FbpService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\FbpService
 */
final class FbpServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return FbpService::class;
    }

    /**
     * @covers ::warehouseList
     */
    public function testWarehouseList(): void
    {
        $this->quickTest(
            'warehouseList',
            [],
            ['POST', '/v1/fbp/warehouse/list', '{}'],
            '{"warehouses":[]}',
            static function (array $result): void {
                self::assertSame(['warehouses' => []], $result);
            }
        );
    }

    /**
     * @covers ::draftList
     */
    public function testDraftList(): void
    {
        $this->quickTest(
            'draftList',
            [50, 123],
            ['POST', '/v1/fbp/draft/list', '{"count":50,"last_id":123}'],
            '{"items":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['items' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::draftList
     */
    public function testDraftListDefaults(): void
    {
        $this->quickTest(
            'draftList',
            [],
            ['POST', '/v1/fbp/draft/list', '{"count":100}'],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
            }
        );
    }

    /**
     * @covers ::draftGet
     */
    public function testDraftGet(): void
    {
        $json = '{"id":1,"supply_id":"019a2b3c","status":"NEW","row_version":2}';

        $this->quickTest(
            'draftGet',
            ['019a2b3c'],
            ['POST', '/v1/fbp/draft/get', '{"supply_id":"019a2b3c"}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::draftDirectCreate
     */
    public function testDraftDirectCreate(): void
    {
        $this->quickTest(
            'draftDirectCreate',
            [
                [
                    'bundle_id'           => 'bundle-1',
                    'warehouse_id'        => '123',
                    'package_units_count' => '5',
                    'delivery_details'    => [
                        'timeslot_start' => '2026-08-25T10:00:00Z',
                        // must be filtered out
                        'foo'            => 'bar',
                    ],
                    // must be filtered out
                    'baz' => 'qux',
                ],
            ],
            [
                'POST',
                '/v1/fbp/draft/direct/create',
                '{"bundle_id":"bundle-1","warehouse_id":123,"package_units_count":5,"delivery_details":{"timeslot_start":"2026-08-25T10:00:00Z"}}',
            ],
            '{"draft_id":1,"supply_id":"019a2b3c","row_version":1}',
            static function (array $result): void {
                self::assertSame(['draft_id' => 1, 'supply_id' => '019a2b3c', 'row_version' => 1], $result);
            }
        );
    }

    /**
     * @covers ::draftDirectDelete
     */
    public function testDraftDirectDelete(): void
    {
        $this->quickTest(
            'draftDirectDelete',
            ['019a2b3c'],
            ['POST', '/v1/fbp/draft/direct/delete', '{"supply_id":"019a2b3c"}'],
            '{"row_version":2}',
            static function (array $result): void {
                self::assertSame(['row_version' => 2], $result);
            }
        );
    }

    /**
     * @covers ::draftDirectRegistrate
     */
    public function testDraftDirectRegistrate(): void
    {
        $this->quickTest(
            'draftDirectRegistrate',
            ['019a2b3c', 2],
            [
                'POST',
                '/v1/fbp/draft/direct/registrate',
                '{"supply_id":"019a2b3c","row_version":2}',
            ],
            '{"is_error":false,"row_version":3}',
            static function (array $result): void {
                self::assertSame(['is_error' => false, 'row_version' => 3], $result);
            }
        );
    }

    /**
     * @covers ::draftDirectProductValidate
     */
    public function testDraftDirectProductValidate(): void
    {
        $this->quickTest(
            'draftDirectProductValidate',
            [
                123,
                [
                    [
                        'sku'   => '160249683',
                        'count' => '2',
                        // must be filtered out
                        'foo'   => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/fbp/draft/direct/product/validate',
                '{"warehouse_id":123,"skus":[{"sku":160249683,"count":2}]}',
            ],
            '{"bundle_id":"bundle-1","bundle_generated":true}',
            static function (array $result): void {
                self::assertSame(['bundle_id' => 'bundle-1', 'bundle_generated' => true], $result);
            }
        );
    }

    /**
     * @covers ::draftDirectSellerDlvCreate
     */
    public function testDraftDirectSellerDlvCreate(): void
    {
        $this->quickTest(
            'draftDirectSellerDlvCreate',
            [
                [
                    'bundle_id'           => 'bundle-1',
                    'warehouse_id'        => 123,
                    'package_units_count' => 5,
                    'delivery_details'    => [
                        'timeslot_start' => '2026-08-25T10:00:00Z',
                        'driver_name'    => 'Иванов И.И.',
                        'vehicle_number' => 'А123БВ777',
                        'vehicle_type'   => 'truck',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/fbp/draft/direct/seller-dlv/create',
                '{"bundle_id":"bundle-1","warehouse_id":123,"package_units_count":5,"delivery_details":{"timeslot_start":"2026-08-25T10:00:00Z","driver_name":"Иванов И.И.","vehicle_number":"А123БВ777","vehicle_type":"truck"}}',
            ],
            '{"draft_id":1}',
            static function (array $result): void {
                self::assertSame(['draft_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::draftDirectSellerDlvEdit
     */
    public function testDraftDirectSellerDlvEdit(): void
    {
        $this->quickTest(
            'draftDirectSellerDlvEdit',
            [
                [
                    'supply_id'      => '019a2b3c',
                    'row_version'    => '2',
                    'driver_name'    => 'Петров П.П.',
                    'vehicle_number' => 'А123БВ777',
                    'vehicle_type'   => 'truck',
                    // must be filtered out
                    'foo'            => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/fbp/draft/direct/seller-dlv/edit',
                '{"supply_id":"019a2b3c","row_version":2,"driver_name":"Петров П.П.","vehicle_number":"А123БВ777","vehicle_type":"truck"}',
            ],
            '{"is_error":false}',
            static function (array $result): void {
                self::assertSame(['is_error' => false], $result);
            }
        );
    }

    /**
     * @covers ::draftDirectTplDlvCreate
     */
    public function testDraftDirectTplDlvCreate(): void
    {
        $this->quickTest(
            'draftDirectTplDlvCreate',
            [
                [
                    'bundle_id'           => 'bundle-1',
                    'warehouse_id'        => 123,
                    'package_units_count' => 5,
                    'delivery_details'    => [
                        'timeslot_start'         => '2026-08-25T10:00:00Z',
                        'tracking_number'        => 'TN-1',
                        'transport_company_name' => 'СДЭК',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/fbp/draft/direct/tpl-dlv/create',
                '{"bundle_id":"bundle-1","warehouse_id":123,"package_units_count":5,"delivery_details":{"timeslot_start":"2026-08-25T10:00:00Z","tracking_number":"TN-1","transport_company_name":"СДЭК"}}',
            ],
            '{"draft_id":1}',
            static function (array $result): void {
                self::assertSame(['draft_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::draftDirectTplDlvEdit
     */
    public function testDraftDirectTplDlvEdit(): void
    {
        $this->quickTest(
            'draftDirectTplDlvEdit',
            [
                [
                    'supply_id'              => '019a2b3c',
                    'row_version'            => '2',
                    'tracking_number'        => 'TN-2',
                    'transport_company_name' => 'СДЭК',
                    // must be filtered out
                    'foo'                    => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/fbp/draft/direct/tpl-dlv/edit',
                '{"supply_id":"019a2b3c","row_version":2,"tracking_number":"TN-2","transport_company_name":"СДЭК"}',
            ],
            '{"is_error":false}',
            static function (array $result): void {
                self::assertSame(['is_error' => false], $result);
            }
        );
    }

    /**
     * @covers ::draftDirectTimeslotGet
     */
    public function testDraftDirectTimeslotGet(): void
    {
        $this->quickTest(
            'draftDirectTimeslotGet',
            ['bundle-1', 123, '2026-08-25T00:00:00Z', '2026-08-26T00:00:00Z'],
            [
                'POST',
                '/v1/fbp/draft/direct/timeslot/get',
                '{"bundle_id":"bundle-1","warehouse_id":123,"interval_start":"2026-08-25T00:00:00Z","interval_end":"2026-08-26T00:00:00Z"}',
            ],
            '{"timeslots":[]}',
            static function (array $result): void {
                self::assertSame(['timeslots' => []], $result);
            }
        );
    }

    /**
     * @covers ::draftDirectTimeslotEdit
     */
    public function testDraftDirectTimeslotEdit(): void
    {
        $this->quickTest(
            'draftDirectTimeslotEdit',
            ['019a2b3c', 2, '2026-08-25T10:00:00Z'],
            [
                'POST',
                '/v1/fbp/draft/direct/timeslot/edit',
                '{"supply_id":"019a2b3c","row_version":2,"timeslot_start":"2026-08-25T10:00:00Z"}',
            ],
            '{"row_version":3}',
            static function (array $result): void {
                self::assertSame(['row_version' => 3], $result);
            }
        );
    }

    /**
     * @covers ::draftDropOffCreate
     */
    public function testDraftDropOffCreate(): void
    {
        $this->quickTest(
            'draftDropOffCreate',
            [
                [
                    'bundle_id'           => 'bundle-1',
                    'warehouse_id'        => 123,
                    'package_units_count' => 5,
                    'delivery_details'    => [
                        'drop_off_date'          => '2026-08-25',
                        'drop_off_point_id'      => '456',
                        'drop_off_province_uuid' => 'uuid-1',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/fbp/draft/drop-off/create',
                '{"bundle_id":"bundle-1","warehouse_id":123,"package_units_count":5,"delivery_details":{"drop_off_date":"2026-08-25","drop_off_point_id":456,"drop_off_province_uuid":"uuid-1"}}',
            ],
            '{"draft_id":1}',
            static function (array $result): void {
                self::assertSame(['draft_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::draftDropOffDelete
     */
    public function testDraftDropOffDelete(): void
    {
        $this->quickTest(
            'draftDropOffDelete',
            ['019a2b3c'],
            ['POST', '/v1/fbp/draft/drop-off/delete', '{"supply_id":"019a2b3c"}'],
            '{"row_version":2}',
            static function (array $result): void {
                self::assertSame(['row_version' => 2], $result);
            }
        );
    }

    /**
     * @covers ::draftDropOffDlvEdit
     */
    public function testDraftDropOffDlvEdit(): void
    {
        $this->quickTest(
            'draftDropOffDlvEdit',
            [
                [
                    'supply_id'              => '019a2b3c',
                    'row_version'            => '2',
                    'drop_off_date'          => '2026-08-26',
                    'drop_off_point_id'      => '456',
                    'drop_off_province_uuid' => 'uuid-1',
                    // must be filtered out
                    'foo'                    => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/fbp/draft/drop-off/dlv/edit',
                '{"supply_id":"019a2b3c","row_version":2,"drop_off_date":"2026-08-26","drop_off_point_id":456,"drop_off_province_uuid":"uuid-1"}',
            ],
            '{"row_version":3}',
            static function (array $result): void {
                self::assertSame(['row_version' => 3], $result);
            }
        );
    }

    /**
     * @covers ::draftDropOffRegistrate
     */
    public function testDraftDropOffRegistrate(): void
    {
        $this->quickTest(
            'draftDropOffRegistrate',
            ['019a2b3c', 2],
            [
                'POST',
                '/v1/fbp/draft/drop-off/registrate',
                '{"supply_id":"019a2b3c","row_version":2}',
            ],
            '{"is_error":false}',
            static function (array $result): void {
                self::assertSame(['is_error' => false], $result);
            }
        );
    }

    /**
     * @covers ::draftDropOffProvinceList
     */
    public function testDraftDropOffProvinceList(): void
    {
        $this->quickTest(
            'draftDropOffProvinceList',
            [123],
            ['POST', '/v1/fbp/draft/drop-off/province/list', '{"warehouse_id":123}'],
            '{"provinces":[]}',
            static function (array $result): void {
                self::assertSame(['provinces' => []], $result);
            }
        );
    }

    /**
     * @covers ::draftDropOffPointList
     */
    public function testDraftDropOffPointList(): void
    {
        $this->quickTest(
            'draftDropOffPointList',
            [123, 'uuid-1', 50, 2],
            [
                'POST',
                '/v1/fbp/draft/drop-off/point/list',
                '{"warehouse_id":123,"province_uuid":"uuid-1","page_size":50,"next_page_number":2}',
            ],
            '{"drop_off_points":[]}',
            static function (array $result): void {
                self::assertSame(['drop_off_points' => []], $result);
            }
        );
    }

    /**
     * @covers ::draftDropOffPointTimetable
     */
    public function testDraftDropOffPointTimetable(): void
    {
        $this->quickTest(
            'draftDropOffPointTimetable',
            [123, 'uuid-1', 456],
            [
                'POST',
                '/v1/fbp/draft/drop-off/point/timetable',
                '{"warehouse_id":123,"province_uuid":"uuid-1","drop_off_point_id":456}',
            ],
            '{"calendar":[]}',
            static function (array $result): void {
                self::assertSame(['calendar' => []], $result);
            }
        );
    }

    /**
     * @covers ::draftDropOffProductValidate
     */
    public function testDraftDropOffProductValidate(): void
    {
        $this->quickTest(
            'draftDropOffProductValidate',
            [123, [['sku' => 160249683, 'count' => 2]]],
            [
                'POST',
                '/v1/fbp/draft/drop-off/product/validate',
                '{"warehouse_id":123,"skus":[{"sku":160249683,"count":2}]}',
            ],
            '{"bundle_id":"bundle-1"}',
            static function (array $result): void {
                self::assertSame(['bundle_id' => 'bundle-1'], $result);
            }
        );
    }

    /**
     * @covers ::draftPickUpCreate
     */
    public function testDraftPickUpCreate(): void
    {
        $this->quickTest(
            'draftPickUpCreate',
            [
                [
                    'bundle_id'           => 'bundle-1',
                    'warehouse_id'        => 123,
                    'package_units_count' => 5,
                    'delivery_details'    => [
                        'address'      => 'Москва, ул. Ленина, 1',
                        'comment'      => 'звонить заранее',
                        'date'         => '2026-08-25',
                        'sender_name'  => 'Иванов И.И.',
                        'sender_phone' => '+79001234567',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/fbp/draft/pick-up/create',
                '{"bundle_id":"bundle-1","warehouse_id":123,"package_units_count":5,"delivery_details":{"address":"Москва, ул. Ленина, 1","comment":"звонить заранее","date":"2026-08-25","sender_name":"Иванов И.И.","sender_phone":"+79001234567"}}',
            ],
            '{"draft_id":1}',
            static function (array $result): void {
                self::assertSame(['draft_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::draftPickUpDelete
     */
    public function testDraftPickUpDelete(): void
    {
        $this->quickTest(
            'draftPickUpDelete',
            ['019a2b3c'],
            ['POST', '/v1/fbp/draft/pick-up/delete', '{"supply_id":"019a2b3c"}'],
            '{"row_version":2}',
            static function (array $result): void {
                self::assertSame(['row_version' => 2], $result);
            }
        );
    }

    /**
     * @covers ::draftPickUpDlvEdit
     */
    public function testDraftPickUpDlvEdit(): void
    {
        $this->quickTest(
            'draftPickUpDlvEdit',
            [
                [
                    'supply_id'      => '019a2b3c',
                    'row_version'    => '2',
                    'pickup_details' => [
                        'address'      => 'Москва, ул. Ленина, 1',
                        'comment'      => '',
                        'date'         => '2026-08-26',
                        'sender_name'  => 'Иванов И.И.',
                        'sender_phone' => '+79001234567',
                        // must be filtered out
                        'foo'          => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/fbp/draft/pick-up/dlv/edit',
                '{"supply_id":"019a2b3c","row_version":2,"pickup_details":{"address":"Москва, ул. Ленина, 1","comment":"","date":"2026-08-26","sender_name":"Иванов И.И.","sender_phone":"+79001234567"}}',
            ],
            '{"row_version":3}',
            static function (array $result): void {
                self::assertSame(['row_version' => 3], $result);
            }
        );
    }

    /**
     * @covers ::draftPickUpProductValidate
     */
    public function testDraftPickUpProductValidate(): void
    {
        $this->quickTest(
            'draftPickUpProductValidate',
            [123, [['sku' => 160249683, 'count' => 2]]],
            [
                'POST',
                '/v1/fbp/draft/pick-up/product/validate',
                '{"warehouse_id":123,"skus":[{"sku":160249683,"count":2}]}',
            ],
            '{"bundle_id":"bundle-1"}',
            static function (array $result): void {
                self::assertSame(['bundle_id' => 'bundle-1'], $result);
            }
        );
    }

    /**
     * @covers ::draftPickUpRegistrate
     */
    public function testDraftPickUpRegistrate(): void
    {
        $this->quickTest(
            'draftPickUpRegistrate',
            ['019a2b3c', 2],
            [
                'POST',
                '/v1/fbp/draft/pick-up/registrate',
                '{"supply_id":"019a2b3c","row_version":2}',
            ],
            '{"is_error":false}',
            static function (array $result): void {
                self::assertSame(['is_error' => false], $result);
            }
        );
    }

    /**
     * @covers ::orderList
     */
    public function testOrderList(): void
    {
        $this->quickTest(
            'orderList',
            [50],
            ['POST', '/v1/fbp/order/list', '{"count":50}'],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
            }
        );
    }

    /**
     * @covers ::orderGet
     */
    public function testOrderGet(): void
    {
        $this->quickTest(
            'orderGet',
            ['019a2b3c'],
            ['POST', '/v1/fbp/order/get', '{"supply_id":"019a2b3c"}'],
            '{"id":1,"supply_id":"019a2b3c"}',
            static function (array $result): void {
                self::assertSame(['id' => 1, 'supply_id' => '019a2b3c'], $result);
            }
        );
    }

    /**
     * @covers ::orderDirectCancel
     */
    public function testOrderDirectCancel(): void
    {
        $this->quickTest(
            'orderDirectCancel',
            ['019a2b3c'],
            ['POST', '/v1/fbp/order/direct/cancel', '{"supply_id":"019a2b3c"}'],
            '{"is_error":false}',
            static function (array $result): void {
                self::assertSame(['is_error' => false], $result);
            }
        );
    }

    /**
     * @covers ::orderDirectSellerDlvEdit
     */
    public function testOrderDirectSellerDlvEdit(): void
    {
        $this->quickTest(
            'orderDirectSellerDlvEdit',
            [
                [
                    'supply_id'      => '019a2b3c',
                    'row_version'    => 2,
                    'driver_name'    => 'Петров П.П.',
                    'vehicle_number' => 'А123БВ777',
                    'vehicle_type'   => 'truck',
                ],
            ],
            [
                'POST',
                '/v1/fbp/order/direct/seller-dlv/edit',
                '{"supply_id":"019a2b3c","row_version":2,"driver_name":"Петров П.П.","vehicle_number":"А123БВ777","vehicle_type":"truck"}',
            ],
            '{"is_error":false}',
            static function (array $result): void {
                self::assertSame(['is_error' => false], $result);
            }
        );
    }

    /**
     * @covers ::orderDirectTimeslotEdit
     */
    public function testOrderDirectTimeslotEdit(): void
    {
        $this->quickTest(
            'orderDirectTimeslotEdit',
            ['019a2b3c', 2, '2026-08-25T10:00:00Z'],
            [
                'POST',
                '/v1/fbp/order/direct/timeslot/edit',
                '{"supply_id":"019a2b3c","row_version":2,"timeslot_start":"2026-08-25T10:00:00Z"}',
            ],
            '{"row_version":3}',
            static function (array $result): void {
                self::assertSame(['row_version' => 3], $result);
            }
        );
    }

    /**
     * @covers ::orderDirectTimeslotList
     */
    public function testOrderDirectTimeslotList(): void
    {
        $this->quickTest(
            'orderDirectTimeslotList',
            ['019a2b3c', '2026-08-25T00:00:00Z', '2026-08-26T00:00:00Z'],
            [
                'POST',
                '/v1/fbp/order/direct/timeslot/list',
                '{"supply_id":"019a2b3c","interval_start":"2026-08-25T00:00:00Z","interval_end":"2026-08-26T00:00:00Z"}',
            ],
            '{"timeslots":[]}',
            static function (array $result): void {
                self::assertSame(['timeslots' => []], $result);
            }
        );
    }

    /**
     * @covers ::orderDropOffCancel
     */
    public function testOrderDropOffCancel(): void
    {
        $this->quickTest(
            'orderDropOffCancel',
            ['019a2b3c'],
            ['POST', '/v1/fbp/order/drop-off/cancel', '{"supply_id":"019a2b3c"}'],
            '{"is_error":false}',
            static function (array $result): void {
                self::assertSame(['is_error' => false], $result);
            }
        );
    }

    /**
     * @covers ::orderDropOffDlvEdit
     */
    public function testOrderDropOffDlvEdit(): void
    {
        $this->quickTest(
            'orderDropOffDlvEdit',
            ['019a2b3c', 2, '2026-08-26'],
            [
                'POST',
                '/v1/fbp/order/drop-off/dlv/edit',
                '{"supply_id":"019a2b3c","row_version":2,"drop_off_date":"2026-08-26"}',
            ],
            '{"row_version":3}',
            static function (array $result): void {
                self::assertSame(['row_version' => 3], $result);
            }
        );
    }

    /**
     * @covers ::orderDropOffTimetable
     */
    public function testOrderDropOffTimetable(): void
    {
        $this->quickTest(
            'orderDropOffTimetable',
            [123, 'uuid-1', 456],
            [
                'POST',
                '/v1/fbp/order/drop-off/timetable',
                '{"warehouse_id":123,"province_uuid":"uuid-1","drop_off_point_id":456}',
            ],
            '{"calendar":[]}',
            static function (array $result): void {
                self::assertSame(['calendar' => []], $result);
            }
        );
    }

    /**
     * @covers ::orderPickUpCancel
     */
    public function testOrderPickUpCancel(): void
    {
        $this->quickTest(
            'orderPickUpCancel',
            ['019a2b3c'],
            ['POST', '/v1/fbp/order/pick-up/cancel', '{"supply_id":"019a2b3c"}'],
            '{"is_error":false}',
            static function (array $result): void {
                self::assertSame(['is_error' => false], $result);
            }
        );
    }

    /**
     * @covers ::orderPickUpDlvEdit
     */
    public function testOrderPickUpDlvEdit(): void
    {
        $this->quickTest(
            'orderPickUpDlvEdit',
            [
                [
                    'supply_id'      => '019a2b3c',
                    'row_version'    => '2',
                    'pickup_details' => [
                        'sender_name'  => 'Иванов И.И.',
                        'sender_phone' => '+79001234567',
                        // must be filtered out
                        'address'      => 'ignored',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/fbp/order/pick-up/dlv/edit',
                '{"supply_id":"019a2b3c","row_version":2,"pickup_details":{"sender_name":"Иванов И.И.","sender_phone":"+79001234567"}}',
            ],
            '{"is_error":false}',
            static function (array $result): void {
                self::assertSame(['is_error' => false], $result);
            }
        );
    }

    /**
     * @covers ::actFromCreate
     */
    public function testActFromCreate(): void
    {
        $this->quickTest(
            'actFromCreate',
            ['019a2b3c'],
            ['POST', '/v1/fbp/act-from/create', '{"supply_id":"019a2b3c"}'],
            '{"is_success":true,"file_uuid":"uuid-1"}',
            static function (array $result): void {
                self::assertSame(['is_success' => true, 'file_uuid' => 'uuid-1'], $result);
            }
        );
    }

    /**
     * @covers ::actFromGet
     */
    public function testActFromGet(): void
    {
        $this->quickTest(
            'actFromGet',
            ['uuid-1'],
            ['POST', '/v1/fbp/act-from/get', '{"file_uuid":"uuid-1"}'],
            '{"status":"EXIST","cdn_url":"https://cdn.ozone.ru/act.pdf"}',
            static function (array $result): void {
                self::assertSame(['status' => 'EXIST', 'cdn_url' => 'https://cdn.ozone.ru/act.pdf'], $result);
            }
        );
    }

    /**
     * @covers ::actToCreate
     */
    public function testActToCreate(): void
    {
        $this->quickTest(
            'actToCreate',
            ['019a2b3c'],
            ['POST', '/v1/fbp/act-to/create', '{"supply_id":"019a2b3c"}'],
            '{"code":"act-code"}',
            static function (array $result): void {
                self::assertSame(['code' => 'act-code'], $result);
            }
        );
    }

    /**
     * @covers ::actToGet
     */
    public function testActToGet(): void
    {
        $this->quickTest(
            'actToGet',
            ['019a2b3c', 'act-code'],
            [
                'POST',
                '/v1/fbp/act-to/get',
                '{"supply_id":"019a2b3c","code":"act-code"}',
            ],
            '{"state":"FINISHED","label_url":"https://cdn.ozone.ru/act.pdf"}',
            static function (array $result): void {
                self::assertSame(['state' => 'FINISHED', 'label_url' => 'https://cdn.ozone.ru/act.pdf'], $result);
            }
        );
    }

    /**
     * @covers ::archiveGet
     */
    public function testArchiveGet(): void
    {
        $this->quickTest(
            'archiveGet',
            ['019a2b3c'],
            ['POST', '/v1/fbp/archive/get', '{"supply_id":"019a2b3c"}'],
            '{"id":1,"status":"COMPLETED"}',
            static function (array $result): void {
                self::assertSame(['id' => 1, 'status' => 'COMPLETED'], $result);
            }
        );
    }

    /**
     * @covers ::archiveList
     */
    public function testArchiveList(): void
    {
        $this->quickTest(
            'archiveList',
            [50, 123],
            ['POST', '/v1/fbp/archive/list', '{"count":"50","last_id":"123"}'],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
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
            ['019a2b3c'],
            ['POST', '/v1/fbp/label/create', '{"supply_id":"019a2b3c"}'],
            '{"code":"label-code"}',
            static function (array $result): void {
                self::assertSame(['code' => 'label-code'], $result);
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
            ['019a2b3c', 'label-code'],
            [
                'POST',
                '/v1/fbp/label/get',
                '{"supply_id":"019a2b3c","code":"label-code"}',
            ],
            '{"state":"FINISHED","label_url":"https://cdn.ozone.ru/label.pdf"}',
            static function (array $result): void {
                self::assertSame(['state' => 'FINISHED', 'label_url' => 'https://cdn.ozone.ru/label.pdf'], $result);
            }
        );
    }
}
