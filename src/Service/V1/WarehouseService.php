<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * @psalm-type TWarehouse = array{
 *     warehouse_id?: int,
 *     name?: string,
 *     status?: string,
 *     first_mile_type?: array{
 *         dropoff_point_id?: string,
 *         dropoff_timeslot_id?: int,
 *         first_mile_is_changing?: bool,
 *         first_mile_type?: 'DropOff'|'Pickup'
 *     },
 *     has_entrusted_acceptance?: bool,
 *     has_postings_limit?: bool,
 *     is_rfbs?: bool,
 *     is_karantin?: bool,
 *     is_kgt?: bool,
 *     is_economy?: bool,
 *     is_able_to_set_price?: bool,
 *     is_presorted?: bool,
 *     is_timetable_editable?: bool,
 *     can_print_act_in_advance?: bool,
 *     min_postings_limit?: int,
 *     postings_limit?: int,
 *     min_working_days?: int,
 *     working_days?: list<string>
 * }
 * @psalm-type TOperationResult = array{operation_id?: string}
 * @psalm-type TOperationStatus = array{
 *     status?: 'UNSPECIFIED'|'IN_PROGRESS'|'SUCCESS'|'ERROR',
 *     type?: 'UNSPECIFIED'|'CREATE_FBS_WAREHOUSE'|'UPDATE_FBS_WAREHOUSE'|'SET_FIRST_MILE'|'WAREHOUSE_ENABLE_DISABLE'|'WAREHOUSE_PAUSE_UNPAUSE',
 *     result?: array{entity_id?: int},
 *     error?: array{code?: string, message?: string}
 * }
 * @psalm-type TCoordinates = array{latitude: float, longitude: float}
 * @psalm-type TWorkingDay = 'MONDAY'|'TUESDAY'|'WEDNESDAY'|'THURSDAY'|'FRIDAY'|'SATURDAY'|'SUNDAY'
 * @psalm-type TFbsOptions = array{
 *     comment?: string,
 *     courier_phones?: list<string>,
 *     is_auto_assembly?: bool,
 *     is_waybill_enabled?: bool
 * }
 * @psalm-type TFbsCreateRequest = array{
 *     name: string,
 *     phone: string,
 *     address_coordinates: TCoordinates,
 *     first_mile_type: 'PICK_UP'|'DROP_OFF',
 *     cut_in_time: int,
 *     timeslot_id: int,
 *     is_kgt: bool,
 *     drop_off_point_id?: int,
 *     return_point_id?: int,
 *     working_days?: list<TWorkingDay>,
 *     options?: TFbsOptions
 * }
 * @psalm-type TFbsUpdateRequest = array{
 *     warehouse_id: int,
 *     address_coordinates: TCoordinates,
 *     name?: string,
 *     phone?: string,
 *     working_days?: list<TWorkingDay>,
 *     options?: TFbsOptions
 * }
 * @psalm-type TFirstMileUpdateRequest = array{
 *     warehouse_id: int,
 *     first_mile_type: 'PICK_UP'|'DROP_OFF',
 *     cut_in_time: int,
 *     timeslot_id: int,
 *     drop_off_point_id?: int,
 *     return_point_id?: int
 * }
 * @psalm-type TPointSearch = array{address?: string, types?: list<string>}
 * @psalm-type TPointListResponse = array{points?: list<array>}
 * @psalm-type TReturnPointListResponse = array{
 *     points?: list<array>,
 *     last_id?: int,
 *     has_next?: bool,
 *     is_selected_point_available?: bool
 * }
 * @psalm-type TTimeslotListResponse = array{timeslots?: list<array>, is_pickup_supported?: bool}
 * @psalm-type TPickupHistoryRequest = array{
 *     filter?: array{planned_date?: string, warehouse_id?: list<int>, was_planned?: bool},
 *     cursor?: string,
 *     limit?: int
 * }
 */
class WarehouseService extends AbstractService
{
    private $path = '/v1/warehouse';

    /**
     * @return list<TWarehouse>
     *@deprecated use V2\WarehouseService::list
     *
     */
    public function list(): array
    {
        return $this->request('POST', "{$this->path}/list");
    }

    /**
     * Status of an asynchronous warehouse operation.
     *
     * The response has a top-level `result` key, so it is returned unwrapped.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseOperationStatus
     *
     * @return TOperationStatus
     */
    public function operationStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/operation/status", ['operation_id' => $operationId], true, false);
    }

    /**
     * Archives a warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseArchive
     *
     * @return TOperationResult
     */
    public function archive(int $warehouseId, string $reason, ?int $returnPointId = null): array
    {
        $requestData = [
            'warehouse_id' => $warehouseId,
            'reason'       => $reason,
        ];

        if (null !== $returnPointId) {
            $requestData['return_point_id'] = $returnPointId;
        }

        return $this->request('POST', "{$this->path}/archive", $requestData);
    }

    /**
     * Restores a warehouse from the archive.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseUnarchive
     *
     * @return TOperationResult
     */
    public function unarchive(int $warehouseId, ?int $returnPointId = null): array
    {
        $requestData = ['warehouse_id' => $warehouseId];

        if (null !== $returnPointId) {
            $requestData['return_point_id'] = $returnPointId;
        }

        return $this->request('POST', "{$this->path}/unarchive", $requestData);
    }

    /**
     * Products that cannot be sold from the warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseInvalidProductsGet
     *
     * @return array{warehouse_id?: int, validation_results?: list<array>, last_id?: int, has_next?: bool}
     */
    public function invalidProductsGet(int $warehouseId, ?int $lastId = null): array
    {
        $requestData = ['warehouse_id' => $warehouseId];

        if (null !== $lastId) {
            $requestData['last_id'] = $lastId;
        }

        return $this->request('POST', "{$this->path}/invalid-products/get", $requestData);
    }

    /**
     * Warehouses that have products which cannot be sold.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehousesWithInvalidProducts
     *
     * @return array{warehouse_ids?: list<string>}
     */
    public function warehousesWithInvalidProducts(): array
    {
        return $this->request('POST', "{$this->path}/warehouses-with-invalid-products", '{}');
    }

    /**
     * Ozon warehouses list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseOzonList
     *
     * @param list<string> $warehouseTypes
     *
     * @return array{warehouses?: list<array>}
     */
    public function ozonList(array $warehouseTypes = []): array
    {
        $requestData = [];

        if ($warehouseTypes) {
            $requestData['warehouse_types'] = array_map('strval', $warehouseTypes);
        }

        return $this->request('POST', "{$this->path}/ozon/list", $requestData ?: '{}');
    }

    /**
     * Search for FBO warehouses available for supplies.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFboList
     *
     * @param list<'CREATE_TYPE_CROSSDOCK'|'CREATE_TYPE_DIRECT'> $filterBySupplyType
     *
     * @return array{search?: list<array>}
     */
    public function fboList(string $search = '', array $filterBySupplyType = []): array
    {
        return $this->request('POST', "{$this->path}/fbo/list", [
            'search'               => $search,
            'filter_by_supply_type' => array_map('strval', $filterBySupplyType),
        ]);
    }

    /**
     * Seller FBO warehouses list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFboSellerList
     *
     * @return array{warehouses?: list<array>}
     */
    public function fboSellerList(): array
    {
        return $this->request('POST', "{$this->path}/fbo/seller/list", '{}');
    }

    /**
     * Pauses an rFBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseRfbsPause
     *
     * @return TOperationResult
     */
    public function rfbsPause(int $warehouseId): array
    {
        return $this->request('POST', "{$this->path}/rfbs/pause", ['warehouse_id' => $warehouseId]);
    }

    /**
     * Resumes an rFBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseRfbsUnpause
     *
     * @return TOperationResult
     */
    public function rfbsUnpause(int $warehouseId): array
    {
        return $this->request('POST', "{$this->path}/rfbs/unpause", ['warehouse_id' => $warehouseId]);
    }

    /**
     * Creates an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsCreate
     *
     * @param TFbsCreateRequest $requestData
     *
     * @return TOperationResult
     */
    public function fbsCreate(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'name',
            'phone',
            'address_coordinates',
            'first_mile_type',
            'cut_in_time',
            'timeslot_id',
            'is_kgt',
            'drop_off_point_id',
            'return_point_id',
            'working_days',
            'options',
        ]);

        $requestData = $this->castCoordinates($requestData, 'address_coordinates');
        $requestData = $this->castFbsOptions($requestData);

        return $this->request('POST', "{$this->path}/fbs/create", TypeCaster::castArr($requestData, [
            'name'              => 'str',
            'phone'             => 'str',
            'first_mile_type'   => 'str',
            'cut_in_time'       => 'int',
            'timeslot_id'       => 'int',
            'is_kgt'            => 'bool',
            'drop_off_point_id' => 'int',
            'return_point_id'   => 'int',
            'working_days'      => 'arrOfStr',
        ]));
    }

    /**
     * Updates an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsUpdate
     *
     * @param TFbsUpdateRequest $requestData
     *
     * @return TOperationResult
     */
    public function fbsUpdate(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'warehouse_id',
            'address_coordinates',
            'name',
            'phone',
            'working_days',
            'options',
        ]);

        $requestData = $this->castCoordinates($requestData, 'address_coordinates');
        $requestData = $this->castFbsOptions($requestData);

        return $this->request('POST', "{$this->path}/fbs/update", TypeCaster::castArr($requestData, [
            'warehouse_id' => 'int',
            'name'         => 'str',
            'phone'        => 'str',
            'working_days' => 'arrOfStr',
        ]));
    }

    /**
     * Changes the first mile of an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsFirstMileUpdate
     *
     * @param TFirstMileUpdateRequest $requestData
     *
     * @return TOperationResult
     */
    public function fbsFirstMileUpdate(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'warehouse_id',
                'first_mile_type',
                'cut_in_time',
                'timeslot_id',
                'drop_off_point_id',
                'return_point_id',
            ]),
            [
                'warehouse_id'      => 'int',
                'first_mile_type'   => 'str',
                'cut_in_time'       => 'int',
                'timeslot_id'       => 'int',
                'drop_off_point_id' => 'int',
                'return_point_id'   => 'int',
            ]
        );

        return $this->request('POST', "{$this->path}/fbs/first-mile/update", $requestData);
    }

    /**
     * Drop-off points available when creating an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsCreateDropOffList
     *
     * @param array{country_code: string, is_kgt: bool, coordinates?: TCoordinates, search?: TPointSearch} $requestData
     *
     * @return TPointListResponse
     */
    public function fbsCreateDropOffList(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, ['country_code', 'is_kgt', 'coordinates', 'search']);
        $requestData = $this->castCoordinates($requestData, 'coordinates');
        $requestData = $this->castPointSearch($requestData);

        return $this->request('POST', "{$this->path}/fbs/create/drop-off/list", TypeCaster::castArr($requestData, [
            'country_code' => 'str',
            'is_kgt'       => 'bool',
        ]));
    }

    /**
     * Drop-off points available when updating an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsUpdateDropOffList
     *
     * @param TPointSearch $search
     *
     * @return TPointListResponse
     */
    public function fbsUpdateDropOffList(int $warehouseId, array $search = []): array
    {
        $requestData = $this->castPointSearch([
            'warehouse_id' => $warehouseId,
            'search'       => $search,
        ]);

        return $this->request('POST', "{$this->path}/fbs/update/drop-off/list", $requestData);
    }

    /**
     * Drop-off point timeslots available when creating an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsCreateDropOffTimeslotList
     *
     * @return TTimeslotListResponse
     */
    public function fbsCreateDropOffTimeslotList(int $dropOffPointId): array
    {
        return $this->request('POST', "{$this->path}/fbs/create/drop-off/timeslot/list", [
            'drop_off_point_id' => $dropOffPointId,
        ]);
    }

    /**
     * Drop-off point timeslots available when updating an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsUpdateDropOffTimeslotList
     *
     * @return TTimeslotListResponse
     */
    public function fbsUpdateDropOffTimeslotList(int $warehouseId, int $dropOffPointId): array
    {
        return $this->request('POST', "{$this->path}/fbs/update/drop-off/timeslot/list", [
            'warehouse_id'      => $warehouseId,
            'drop_off_point_id' => $dropOffPointId,
        ]);
    }

    /**
     * Pick-up timeslots available when creating an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsCreatePickUpTimeslotList
     *
     * @param TCoordinates $addressCoordinates
     *
     * @return TTimeslotListResponse
     */
    public function fbsCreatePickUpTimeslotList(array $addressCoordinates, bool $isKgt = false): array
    {
        $requestData = $this->castCoordinates([
            'address_coordinates' => $addressCoordinates,
            'is_kgt'              => $isKgt,
        ], 'address_coordinates');

        return $this->request('POST', "{$this->path}/fbs/create/pick-up/timeslot/list", $requestData);
    }

    /**
     * Pick-up timeslots available when updating an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsUpdatePickUpTimeslotList
     *
     * @return TTimeslotListResponse
     */
    public function fbsUpdatePickUpTimeslotList(int $warehouseId): array
    {
        return $this->request('POST', "{$this->path}/fbs/update/pick-up/timeslot/list", [
            'warehouse_id' => $warehouseId,
        ]);
    }

    /**
     * Return points available when creating an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsCreateReturnPointList
     *
     * @param array{coordinates: TCoordinates, country_code: string, limit?: int, last_id?: int, search?: TPointSearch, selected_dropoff_point_id?: int} $requestData
     *
     * @return TReturnPointListResponse
     */
    public function fbsCreateReturnPointList(array $requestData): array
    {
        $requestData = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($requestData, [
                'coordinates',
                'country_code',
                'limit',
                'last_id',
                'search',
                'selected_dropoff_point_id',
            ])
        );

        $requestData = $this->castCoordinates($requestData, 'coordinates');
        $requestData = $this->castPointSearch($requestData);

        return $this->request('POST', "{$this->path}/fbs/create/return-point/list", TypeCaster::castArr($requestData, [
            'country_code'              => 'str',
            'limit'                     => 'int',
            'last_id'                   => 'int',
            'selected_dropoff_point_id' => 'int',
        ]));
    }

    /**
     * Return points available when updating an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsUpdateReturnPointList
     *
     * @param array{warehouse_id: int, limit?: int, last_id?: int, search?: TPointSearch, current_dropoff_point_id?: int, current_return_point_id?: int} $requestData
     *
     * @return TReturnPointListResponse
     */
    public function fbsUpdateReturnPointList(array $requestData): array
    {
        $requestData = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($requestData, [
                'warehouse_id',
                'limit',
                'last_id',
                'search',
                'current_dropoff_point_id',
                'current_return_point_id',
            ])
        );

        $requestData = $this->castPointSearch($requestData);

        return $this->request('POST', "{$this->path}/fbs/update/return-point/list", TypeCaster::castArr($requestData, [
            'warehouse_id'             => 'int',
            'limit'                    => 'int',
            'last_id'                  => 'int',
            'current_dropoff_point_id' => 'int',
            'current_return_point_id'  => 'int',
        ]));
    }

    /**
     * Return mile settings of warehouses.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsReturnMileInfo
     *
     * @param list<int|string> $warehouseIds
     *
     * @return array{return_mile_settings?: list<array>}
     */
    public function fbsReturnMileInfo(array $warehouseIds): array
    {
        return $this->request('POST', "{$this->path}/fbs/return-mile/info", [
            'warehouse_ids' => array_map('strval', $warehouseIds),
        ]);
    }

    /**
     * Checks whether the return mile has to be set.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsReturnMileCheck
     *
     * @param array{country_code: string, first_mile_type: 'PICK_UP'|'DROP_OFF', is_kgt: bool, warehouse_id?: int} $requestData
     *
     * @return array{should_set_return_mile?: bool, unavailability_reasons?: list<string>}
     */
    public function fbsReturnMileCheck(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, ['country_code', 'first_mile_type', 'is_kgt', 'warehouse_id']),
            [
                'country_code'    => 'str',
                'first_mile_type' => 'str',
                'is_kgt'          => 'bool',
                'warehouse_id'    => 'int',
            ]
        );

        return $this->request('POST', "{$this->path}/fbs/return-mile/check", $requestData);
    }

    /**
     * Orders a courier pickup for an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsPickupCourierCreate
     */
    public function fbsPickupCourierCreate(int $warehouseId): array
    {
        return $this->request('POST', "{$this->path}/fbs/pickup/courier/create", ['warehouse_id' => $warehouseId]);
    }

    /**
     * Cancels a courier pickup for an FBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsPickupCourierCancel
     */
    public function fbsPickupCourierCancel(int $warehouseId): array
    {
        return $this->request('POST', "{$this->path}/fbs/pickup/courier/cancel", ['warehouse_id' => $warehouseId]);
    }

    /**
     * Courier pickup history.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsPickupHistoryList
     *
     * @param TPickupHistoryRequest $requestData
     *
     * @return array{history?: list<array>, cursor?: string}
     */
    public function fbsPickupHistoryList(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($requestData, ['filter', 'cursor', 'limit'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['planned_date', 'warehouse_id', 'was_planned']),
                ['planned_date' => 'str', 'warehouse_id' => 'arrOfInt', 'was_planned' => 'bool']
            );
        }

        $requestData = TypeCaster::castArr($requestData, ['cursor' => 'str', 'limit' => 'int']);

        return $this->request('POST', "{$this->path}/fbs/pickup/history/list", $requestData);
    }

    /**
     * Warehouses a courier pickup can be planned for.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseFbsPickupPlanningList
     *
     * @return array{warehouses?: list<array>}
     */
    public function fbsPickupPlanningList(): array
    {
        return $this->request('POST', "{$this->path}/fbs/pickup/planning/list", '{}');
    }

    /**
     * Creates an rFBS warehouse served by a delivery aggregator.
     *
     * Nested `delivery_method` and `timetable_warehouse` are passed as is.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseErfbsAggregatorCreate
     *
     * @param array{name: string, phone: string, address_coordinates: TCoordinates, delivery_method: array, timetable_warehouse: array, is_auto_assembly?: bool, min_order_value?: int} $requestData
     *
     * @return TOperationResult
     */
    public function erfbsAggregatorCreate(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/erfbs/aggregator/create",
            $this->pickErfbsCreate($requestData)
        );
    }

    /**
     * Creates an rFBS warehouse served by a non-integrated carrier.
     *
     * Nested `delivery_method` and `timetable_warehouse` are passed as is.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseErfbsNonIntegratedCreate
     *
     * @param array{name: string, phone: string, address_coordinates: TCoordinates, delivery_method: array, timetable_warehouse: array, is_auto_assembly?: bool, min_order_value?: int} $requestData
     *
     * @return TOperationResult
     */
    public function erfbsNonIntegratedCreate(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/erfbs/non-integrated/create",
            $this->pickErfbsCreate($requestData)
        );
    }

    /**
     * Updates an rFBS warehouse.
     *
     * Nested `timetable_warehouse` is passed as is.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseErfbsUpdate
     *
     * @param array{warehouse_id: int, name?: string, phone?: string, timetable_warehouse?: array, is_auto_assembly?: bool, min_order_value?: int} $requestData
     *
     * @return TOperationResult
     */
    public function erfbsUpdate(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'warehouse_id',
                'name',
                'phone',
                'timetable_warehouse',
                'is_auto_assembly',
                'min_order_value',
            ]),
            [
                'warehouse_id'     => 'int',
                'name'             => 'str',
                'phone'            => 'str',
                'is_auto_assembly' => 'bool',
                'min_order_value'  => 'int',
            ]
        );

        return $this->request('POST', "{$this->path}/erfbs/update", $requestData);
    }

    /**
     * Updates the delivery method of an aggregator rFBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseErfbsAggregatorDeliveryMethodUpdate
     *
     * @param array{warehouse_id: int, delivery_method_id: int, name?: string, courier_comment?: string, courier_phones?: list<string>, is_courier_phone_same_as_warehouse?: bool, cut_in?: int, deliver_to_pvz?: bool, delivery_costs?: array, return_settings?: array} $requestData
     *
     * @return TOperationResult
     */
    public function erfbsAggregatorDeliveryMethodUpdate(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'warehouse_id',
                'delivery_method_id',
                'name',
                'courier_comment',
                'courier_phones',
                'is_courier_phone_same_as_warehouse',
                'cut_in',
                'deliver_to_pvz',
                'delivery_costs',
                'return_settings',
            ]),
            [
                'warehouse_id'                       => 'int',
                'delivery_method_id'                 => 'int',
                'name'                               => 'str',
                'courier_comment'                    => 'str',
                'courier_phones'                     => 'arrOfStr',
                'is_courier_phone_same_as_warehouse' => 'bool',
                'cut_in'                             => 'int',
                'deliver_to_pvz'                     => 'bool',
            ]
        );

        return $this->request('POST', "{$this->path}/erfbs/aggregator/delivery-method/update", $requestData);
    }

    /**
     * Updates the delivery method of a non-integrated rFBS warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_WarehouseErfbsNonIntegratedDeliveryMethodUpdate
     *
     * @param array{warehouse_id: int, delivery_method_id: int, name: string, cut_in: int, courier_cutoff: int, return_settings: array} $requestData
     *
     * @return TOperationResult
     */
    public function erfbsNonIntegratedDeliveryMethodUpdate(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'warehouse_id',
                'delivery_method_id',
                'name',
                'cut_in',
                'courier_cutoff',
                'return_settings',
            ]),
            [
                'warehouse_id'       => 'int',
                'delivery_method_id' => 'int',
                'name'               => 'str',
                'cut_in'             => 'int',
                'courier_cutoff'     => 'int',
            ]
        );

        return $this->request('POST', "{$this->path}/erfbs/non-integrated/delivery-method/update", $requestData);
    }

    /**
     * @param array<array-key, mixed> $requestData
     *
     * @return array<string, mixed>
     */
    private function pickErfbsCreate(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'name',
            'phone',
            'address_coordinates',
            'delivery_method',
            'timetable_warehouse',
            'is_auto_assembly',
            'min_order_value',
        ]);

        $requestData = $this->castCoordinates($requestData, 'address_coordinates');

        return TypeCaster::castArr($requestData, [
            'name'             => 'str',
            'phone'            => 'str',
            'is_auto_assembly' => 'bool',
            'min_order_value'  => 'int',
        ]);
    }

    /**
     * @param array<string, mixed> $requestData
     *
     * @return array<string, mixed>
     */
    private function castCoordinates(array $requestData, string $key): array
    {
        if (isset($requestData[$key])) {
            $requestData[$key] = TypeCaster::castArr(
                ArrayHelper::pick($requestData[$key], ['latitude', 'longitude']),
                ['latitude' => 'float', 'longitude' => 'float']
            );
        }

        return $requestData;
    }

    /**
     * @param array<string, mixed> $requestData
     *
     * @return array<string, mixed>
     */
    private function castFbsOptions(array $requestData): array
    {
        if (isset($requestData['options'])) {
            $requestData['options'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['options'], [
                    'comment',
                    'courier_phones',
                    'is_auto_assembly',
                    'is_waybill_enabled',
                ]),
                [
                    'comment'            => 'str',
                    'courier_phones'     => 'arrOfStr',
                    'is_auto_assembly'   => 'bool',
                    'is_waybill_enabled' => 'bool',
                ]
            );
        }

        return $requestData;
    }

    /**
     * @param array<string, mixed> $requestData
     *
     * @return array<string, mixed>
     */
    private function castPointSearch(array $requestData): array
    {
        if (isset($requestData['search'])) {
            $requestData['search'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['search'], ['address', 'types']),
                ['address' => 'str', 'types' => 'arrOfStr']
            );
        }

        return $requestData;
    }
}
