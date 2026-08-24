<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\CarriageService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\CarriageService
 */
final class CarriageServiceTest extends AbstractTestCase
{
    /**
     * @covers ::create
     */
    public function testCreate(): void
    {
        $this->quickTest(
            'create',
            [
                [
                    'delivery_method_id' => '123',
                    'departure_date'     => '2026-08-25T10:00:00Z',
                    'all_blr_traceable'  => false,
                    // must be filtered out
                    'foo'                => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/carriage/create',
                '{"delivery_method_id":123,"departure_date":"2026-08-25T10:00:00Z","all_blr_traceable":false}',
            ],
            '{"carriage_id":1}',
            static function (array $result): void {
                self::assertSame(['carriage_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::create
     */
    public function testCreateEmpty(): void
    {
        $this->quickTest(
            'create',
            [],
            ['POST', '/v1/carriage/create', '{}'],
            '{"carriage_id":1}',
            static function (array $result): void {
                self::assertSame(['carriage_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::approve
     */
    public function testApprove(): void
    {
        $this->quickTest(
            'approve',
            [1, 3],
            ['POST', '/v1/carriage/approve', '{"carriage_id":1,"containers_count":3}'],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::setPostings
     */
    public function testSetPostings(): void
    {
        $this->quickTest(
            'setPostings',
            [1, ['33920474-0032-1']],
            [
                'POST',
                '/v1/carriage/set-postings',
                '{"carriage_id":1,"posting_numbers":["33920474-0032-1"]}',
            ]
        );
    }

    /**
     * @covers ::cancel
     */
    public function testCancel(): void
    {
        $this->quickTest(
            'cancel',
            [1],
            ['POST', '/v1/carriage/cancel', '{"carriage_id":1}'],
            '{"carriage_status":"cancelled"}',
            static function (array $result): void {
                self::assertSame(['carriage_status' => 'cancelled'], $result);
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
            [1],
            ['POST', '/v1/carriage/get', '{"carriage_id":1}'],
            '{"carriage_id":1,"status":"new","containers_count":2}',
            static function (array $result): void {
                self::assertSame(['carriage_id' => 1, 'status' => 'new', 'containers_count' => 2], $result);
            }
        );
    }

    /**
     * @covers ::deliveryList
     */
    public function testDeliveryList(): void
    {
        $this->quickTest(
            'deliveryList',
            [123, '2026-08-25'],
            [
                'POST',
                '/v1/carriage/delivery/list',
                '{"delivery_method_id":123,"departure_date":"2026-08-25"}',
            ],
            '{"result":[]}'
        );
    }

    /**
     * @covers ::deliveryList
     */
    public function testDeliveryListEmpty(): void
    {
        $this->quickTest(
            'deliveryList',
            [],
            ['POST', '/v1/carriage/delivery/list', '{}'],
            '{"result":[]}'
        );
    }

    /**
     * @covers ::availableList
     */
    public function testAvailableList(): void
    {
        $this->quickTest(
            'availableList',
            [123],
            [
                'POST',
                '/v1/posting/carriage-available/list',
                '{"delivery_method_id":123}',
            ],
            '{"result":[]}'
        );
    }

    /**
     * @covers ::courierContactSet
     */
    public function testCourierContactSet(): void
    {
        $this->quickTest(
            'courierContactSet',
            [
                [
                    'carriage_id'     => '1',
                    'phone'           => '+79001234567',
                    'wechat_nickname' => 'nick',
                    'comment'         => 'звонить заранее',
                    // must be filtered out
                    'foo'             => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/carriage/courier-contact/set',
                '{"carriage_id":1,"phone":"+79001234567","wechat_nickname":"nick","comment":"звонить заранее"}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::courierContactGet
     */
    public function testCourierContactGet(): void
    {
        $this->quickTest(
            'courierContactGet',
            [1],
            ['POST', '/v1/carriage/courier-contact/get', '{"carriage_id":1}'],
            '{"contact":[]}',
            static function (array $result): void {
                self::assertSame(['contact' => []], $result);
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
                1,
                [
                    [
                        'driver_name'           => 'Иванов И.И.',
                        'driver_phone'          => '+79001234567',
                        'vehicle_license_plate' => 'А123БВ777',
                        'vehicle_model'         => 'ГАЗель',
                        'with_returns'          => true,
                        // must be filtered out
                        'foo'                   => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/carriage/pass/create',
                '{"carriage_id":1,"arrival_passes":[{"driver_name":"Иванов И.И.","driver_phone":"+79001234567","vehicle_license_plate":"А123БВ777","vehicle_model":"ГАЗель","with_returns":true}]}',
            ],
            '{"arrival_pass_ids":["10"]}',
            static function (array $result): void {
                self::assertSame(['arrival_pass_ids' => ['10']], $result);
            }
        );
    }

    /**
     * @covers ::passUpdate
     */
    public function testPassUpdate(): void
    {
        $this->quickTest(
            'passUpdate',
            [
                1,
                [
                    [
                        'id'                    => '10',
                        'driver_name'           => 'Петров П.П.',
                        'driver_phone'          => '+79001234567',
                        'vehicle_license_plate' => 'А123БВ777',
                        'vehicle_model'         => 'ГАЗель',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/carriage/pass/update',
                '{"carriage_id":1,"arrival_passes":[{"id":10,"driver_name":"Петров П.П.","driver_phone":"+79001234567","vehicle_license_plate":"А123БВ777","vehicle_model":"ГАЗель"}]}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::passDelete
     */
    public function testPassDelete(): void
    {
        $this->quickTest(
            'passDelete',
            [1, [10, '11']],
            [
                'POST',
                '/v1/carriage/pass/delete',
                '{"carriage_id":1,"arrival_pass_ids":["10","11"]}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::actDiscrepancyPdf
     */
    public function testActDiscrepancyPdf(): void
    {
        $this->quickTest(
            'actDiscrepancyPdf',
            [1],
            ['POST', '/v1/carriage/act-discrepancy/pdf', '{"carriage_id":1}'],
            '{"name":"act.pdf","type":"application/pdf","content":"JVBERi0="}',
            static function (array $result): void {
                self::assertSame('act.pdf', $result['name']);
            }
        );
    }

    /**
     * @covers ::ettnStatus
     */
    public function testEttnStatus(): void
    {
        $this->quickTest(
            'ettnStatus',
            [1],
            ['POST', '/v1/carriage/ettn/status', '{"carriage_id":1}'],
            '{"status":"SUCCESS","errors":[]}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS', 'errors' => []], $result);
            }
        );
    }

    /**
     * @covers ::containerCreate
     */
    public function testContainerCreate(): void
    {
        $this->quickTest(
            'containerCreate',
            [
                [
                    'warehouse_id'     => '123',
                    'containers_count' => '2',
                    'cargo_type'       => 'BOX',
                    'sort_type'        => 'CLUSTER',
                    // must be filtered out
                    'foo'              => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/carriage/container/create',
                '{"warehouse_id":123,"containers_count":2,"cargo_type":"BOX","sort_type":"CLUSTER"}',
            ],
            '{"container_ids":["1","2"]}',
            static function (array $result): void {
                self::assertSame(['container_ids' => ['1', '2']], $result);
            }
        );
    }

    /**
     * @covers ::containerFill
     */
    public function testContainerFill(): void
    {
        $this->quickTest(
            'containerFill',
            [1, ['33920474-0032-1']],
            [
                'POST',
                '/v1/carriage/container/fill',
                '{"container_id":1,"posting_numbers":["33920474-0032-1"]}',
            ],
            '{"task_id":5}',
            static function (array $result): void {
                self::assertSame(['task_id' => 5], $result);
            }
        );
    }

    /**
     * @covers ::containerApprove
     */
    public function testContainerApprove(): void
    {
        $this->quickTest(
            'containerApprove',
            [[1, '2']],
            [
                'POST',
                '/v1/carriage/container/approve',
                '{"container_ids":["1","2"]}',
            ],
            '{"task_id":5}',
            static function (array $result): void {
                self::assertSame(['task_id' => 5], $result);
            }
        );
    }

    /**
     * @covers ::containerPlaceInto
     */
    public function testContainerPlaceInto(): void
    {
        $this->quickTest(
            'containerPlaceInto',
            [1, [2, 3]],
            [
                'POST',
                '/v1/carriage/container/place-into',
                '{"parent_container_id":1,"child_container_ids":["2","3"]}',
            ],
            '{"task_id":5}',
            static function (array $result): void {
                self::assertSame(['task_id' => 5], $result);
            }
        );
    }

    /**
     * @covers ::containerRemovePostings
     */
    public function testContainerRemovePostings(): void
    {
        $this->quickTest(
            'containerRemovePostings',
            [1, ['33920474-0032-1']],
            [
                'POST',
                '/v1/carriage/container/remove-postings',
                '{"container_id":1,"posting_numbers":["33920474-0032-1"]}',
            ],
            '{"task_id":5}',
            static function (array $result): void {
                self::assertSame(['task_id' => 5], $result);
            }
        );
    }

    /**
     * @covers ::containerRemoveFrom
     */
    public function testContainerRemoveFrom(): void
    {
        $this->quickTest(
            'containerRemoveFrom',
            [1, [2]],
            [
                'POST',
                '/v1/carriage/container/remove-from',
                '{"parent_container_id":1,"child_container_ids":["2"]}',
            ],
            '{"task_id":5}',
            static function (array $result): void {
                self::assertSame(['task_id' => 5], $result);
            }
        );
    }

    /**
     * @covers ::containerCancel
     */
    public function testContainerCancel(): void
    {
        $this->quickTest(
            'containerCancel',
            [[1]],
            ['POST', '/v1/carriage/container/cancel', '{"container_ids":["1"]}'],
            '{"task_id":5}',
            static function (array $result): void {
                self::assertSame(['task_id' => 5], $result);
            }
        );
    }

    /**
     * @covers ::containerList
     */
    public function testContainerList(): void
    {
        $this->quickTest(
            'containerList',
            [
                [
                    'filter' => [
                        'created_from' => '2026-08-01T00:00:00Z',
                        'created_to'   => '2026-08-08T00:00:00Z',
                        'sort_type'    => 'CLUSTER',
                        'cargo_type'   => 'BOX',
                        'statuses'     => ['NEW'],
                        'warehouse_id' => '123',
                        // must be filtered out
                        'foo'          => 'bar',
                    ],
                    'cursor'   => 'prev-cursor',
                    'limit'    => '50',
                    'sort_dir' => 'DESC',
                ],
            ],
            [
                'POST',
                '/v1/carriage/container/list',
                '{"limit":50,"sort_dir":"DESC","filter":{"created_from":"2026-08-01T00:00:00Z","created_to":"2026-08-08T00:00:00Z","sort_type":"CLUSTER","cargo_type":"BOX","statuses":["NEW"],"warehouse_id":123},"cursor":"prev-cursor"}',
            ],
            '{"containers":[],"cursor":""}',
            static function (array $result): void {
                self::assertSame(['containers' => [], 'cursor' => ''], $result);
            }
        );
    }

    /**
     * @covers ::containerGet
     */
    public function testContainerGet(): void
    {
        $this->quickTest(
            'containerGet',
            [1],
            ['POST', '/v1/carriage/container/get', '{"container_id":1}'],
            '{"container_id":1,"status":"NEW"}',
            static function (array $result): void {
                self::assertSame(['container_id' => 1, 'status' => 'NEW'], $result);
            }
        );
    }

    /**
     * @covers ::containerStatusGet
     */
    public function testContainerStatusGet(): void
    {
        $this->quickTest(
            'containerStatusGet',
            [[1, 2]],
            [
                'POST',
                '/v1/carriage/container/status/get',
                '{"container_ids":["1","2"]}',
            ],
            '{"containers":[]}',
            static function (array $result): void {
                self::assertSame(['containers' => []], $result);
            }
        );
    }

    /**
     * @covers ::containerTaskInfo
     */
    public function testContainerTaskInfo(): void
    {
        $this->quickTest(
            'containerTaskInfo',
            [5],
            ['POST', '/v1/carriage/container/task/info', '{"task_id":5}'],
            '{"status":"SUCCESS"}',
            static function (array $result): void {
                self::assertSame(['status' => 'SUCCESS'], $result);
            }
        );
    }

    /**
     * @covers ::containerDocumentGet
     */
    public function testContainerDocumentGet(): void
    {
        $this->quickTest(
            'containerDocumentGet',
            [[1]],
            [
                'POST',
                '/v1/carriage/container/document/get',
                '{"container_ids":["1"]}',
            ],
            '{"file_name":"doc.pdf","file_content":"JVBERi0=","content_type":"application/pdf"}',
            static function (array $result): void {
                self::assertSame('doc.pdf', $result['file_name']);
            }
        );
    }

    /**
     * @covers ::containerLabelGet
     */
    public function testContainerLabelGet(): void
    {
        $this->quickTest(
            'containerLabelGet',
            [[1]],
            [
                'POST',
                '/v1/carriage/container/label/get',
                '{"container_ids":["1"]}',
            ],
            '{"content":{"file_name":"label.pdf"},"error_containers":[]}',
            static function (array $result): void {
                self::assertSame(['content' => ['file_name' => 'label.pdf'], 'error_containers' => []], $result);
            }
        );
    }

    protected function getClass(): string
    {
        return CarriageService::class;
    }
}
