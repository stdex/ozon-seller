<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * FBP supplies: drafts, orders, acts, labels.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TSkuItem = array{sku: int, count: int}
 * @psalm-type TCreateResponse = array{
 *     draft_id?: int,
 *     supply_id?: string,
 *     row_version?: int
 * }
 * @psalm-type TDeleteResponse = array{
 *     row_version?: int,
 *     cancellation_state?: array{
 *         cancellation_status?: 'STATUS_UNSPECIFIED'|'CONFIRMATION'|'CANCELED'|'NOT_CANCELED',
 *         cancellation_error?: array
 *     }
 * }
 * @psalm-type TRegistrateResponse = array{
 *     is_error?: bool,
 *     row_version?: int,
 *     error?: array{order_error?: string, bundle_errors?: list<array>}
 * }
 * @psalm-type TEditResponse = array{
 *     is_error?: bool,
 *     row_version?: int,
 *     error?: array
 * }
 * @psalm-type TTimeslotEditResponse = array{
 *     row_version?: int,
 *     error_reasons?: list<string>
 * }
 * @psalm-type TTimeslotListResponse = array{
 *     timeslots?: list<array>,
 *     reasons?: list<string>,
 *     warehouse_timezone_name?: string
 * }
 * @psalm-type TValidateResponse = array{
 *     bundle_id?: string,
 *     bundle_generated?: bool,
 *     approved_items?: list<array>,
 *     rejected_items?: list<array>
 * }
 * @psalm-type TListResponse = array{
 *     items?: list<array>,
 *     last_id?: int,
 *     has_next?: bool
 * }
 * @psalm-type TDeliveryDetails = array{
 *     supply_type?: 'SUPPLY_TYPE_UNSPECIFIED'|'DIRECT_BY_SELLER'|'DIRECT_BY_TPL'|'DROP_OFF'|'PICK_UP',
 *     direct_details?: array,
 *     drop_off_point?: array,
 *     pickup_details?: array
 * }
 * @psalm-type TDraft = array{
 *     id?: int,
 *     supply_id?: string,
 *     bundle_id?: string,
 *     warehouse_id?: int,
 *     status?: 'DRAFT_STATUS_UNSPECIFIED'|'NEW'|'SUPPLY_VARIANT_CONFIRMATION'|'SUPPLY_NOT_CONFIRMED',
 *     package_units_count?: int,
 *     row_version?: int,
 *     created_at?: string,
 *     deleted_at?: string,
 *     editable?: bool,
 *     locked?: bool,
 *     is_cancelable?: bool,
 *     is_deletable?: bool,
 *     is_registration_available?: bool,
 *     delivery_details?: TDeliveryDetails,
 *     cancellation_state?: array,
 *     decline_reason?: array{message?: string, failed_sku_ids?: list<array>}
 * }
 * @psalm-type TOrder = array{
 *     id?: int,
 *     supply_id?: string,
 *     draft_id?: int,
 *     order_number?: string,
 *     bundle_uuid?: string,
 *     warehouse_id?: int,
 *     status?: string,
 *     package_units_count?: int,
 *     row_version?: int,
 *     created_date?: string,
 *     receive_date?: string,
 *     can_be_cancelled?: bool,
 *     has_consignment_note?: bool,
 *     has_label?: bool,
 *     locked?: bool,
 *     attention_reasons?: list<string>,
 *     delivery_details?: TDeliveryDetails,
 *     cancellation_state?: array
 * }
 * @psalm-type TArchive = array{
 *     id?: int,
 *     supply_id?: string,
 *     order_draft_id?: int,
 *     order_number?: string,
 *     bundle_id?: string,
 *     warehouse_id?: int,
 *     status?: 'ARCHIVE_STATUS_UNSPECIFIED'|'COMPLETED'|'REJECTED_AT_SUPPLY_WAREHOUSE'|'CANCELLED_BY_SELLER',
 *     act_file_uuid?: string,
 *     business_flow_type_id?: int,
 *     package_units_count?: int,
 *     row_version?: int,
 *     created_date?: string,
 *     receive_date?: string,
 *     has_act?: bool,
 *     has_label?: bool,
 *     bundle_sku_summary?: array,
 *     delivery_details?: TDeliveryDetails,
 *     decline_reason?: array
 * }
 */
class FbpService extends AbstractService
{
    private $path = '/v1/fbp';

    /**
     * Partner warehouses list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpWarehouseList
     *
     * @return array{warehouses?: list<array>}
     */
    public function warehouseList(): array
    {
        return $this->request('POST', "{$this->path}/warehouse/list", '{}');
    }

    /**
     * Supply drafts list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftList
     *
     * @return TListResponse
     */
    public function draftList(int $count = 100, ?int $lastId = null): array
    {
        return $this->request('POST', "{$this->path}/draft/list", $this->pagination($count, $lastId));
    }

    /**
     * Supply draft info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftGet
     *
     * @return TDraft
     */
    public function draftGet(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/draft/get", ['supply_id' => $supplyId]);
    }

    /**
     * Creates a draft for a supply delivered by Ozon logistics.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDirectCreate
     *
     * @param array{bundle_id: string, warehouse_id: int, package_units_count: int, delivery_details: array{timeslot_start: string}} $requestData
     *
     * @return TCreateResponse
     */
    public function draftDirectCreate(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/draft/direct/create",
            $this->pickDraftCreate($requestData, ['timeslot_start'])
        );
    }

    /**
     * Deletes a draft for a supply delivered by Ozon logistics.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDirectDelete
     *
     * @return TDeleteResponse
     */
    public function draftDirectDelete(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/draft/direct/delete", ['supply_id' => $supplyId]);
    }

    /**
     * Turns a draft delivered by Ozon logistics into a supply order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDirectRegistrate
     *
     * @return TRegistrateResponse
     */
    public function draftDirectRegistrate(string $supplyId, int $rowVersion): array
    {
        return $this->request('POST', "{$this->path}/draft/direct/registrate", [
            'supply_id'   => $supplyId,
            'row_version' => $rowVersion,
        ]);
    }

    /**
     * Validates products for a supply delivered by Ozon logistics.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDirectProductValidate
     *
     * @param list<TSkuItem> $skus
     *
     * @return TValidateResponse
     */
    public function draftDirectProductValidate(int $warehouseId, array $skus): array
    {
        return $this->request(
            'POST',
            "{$this->path}/draft/direct/product/validate",
            $this->pickValidate($warehouseId, $skus)
        );
    }

    /**
     * Creates a draft for a supply delivered by the seller.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDirectSellerDlvCreate
     *
     * @param array{bundle_id: string, warehouse_id: int, package_units_count: int, delivery_details: array{timeslot_start: string, driver_name: string, vehicle_number: string, vehicle_type: string}} $requestData
     *
     * @return TCreateResponse
     */
    public function draftDirectSellerDlvCreate(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/draft/direct/seller-dlv/create",
            $this->pickDraftCreate($requestData, [
                'timeslot_start',
                'driver_name',
                'vehicle_number',
                'vehicle_type',
            ])
        );
    }

    /**
     * Changes the driver and vehicle of a draft delivered by the seller.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDirectSellerDlvEdit
     *
     * @param array{supply_id: string, row_version: int, driver_name: string, vehicle_number: string, vehicle_type: string} $requestData
     *
     * @return TEditResponse
     */
    public function draftDirectSellerDlvEdit(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/draft/direct/seller-dlv/edit",
            $this->pickSellerDlvEdit($requestData)
        );
    }

    /**
     * Creates a draft for a supply delivered by a third-party carrier.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDirectTplDlvCreate
     *
     * @param array{bundle_id: string, warehouse_id: int, package_units_count: int, delivery_details: array{timeslot_start: string, tracking_number: string, transport_company_name: string}} $requestData
     *
     * @return TCreateResponse
     */
    public function draftDirectTplDlvCreate(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/draft/direct/tpl-dlv/create",
            $this->pickDraftCreate($requestData, [
                'timeslot_start',
                'tracking_number',
                'transport_company_name',
            ])
        );
    }

    /**
     * Changes the carrier of a draft delivered by a third-party carrier.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDirectTplDlvEdit
     *
     * @param array{supply_id: string, row_version: int, tracking_number: string, transport_company_name: string} $requestData
     *
     * @return TEditResponse
     */
    public function draftDirectTplDlvEdit(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'supply_id',
                'row_version',
                'tracking_number',
                'transport_company_name',
            ]),
            [
                'supply_id'              => 'str',
                'row_version'            => 'int',
                'tracking_number'        => 'str',
                'transport_company_name' => 'str',
            ]
        );

        return $this->request('POST', "{$this->path}/draft/direct/tpl-dlv/edit", $requestData);
    }

    /**
     * Available timeslots for a draft.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDirectTimeslotGet
     *
     * @return TTimeslotListResponse
     */
    public function draftDirectTimeslotGet(string $bundleId, int $warehouseId, string $intervalStart, string $intervalEnd): array
    {
        return $this->request('POST', "{$this->path}/draft/direct/timeslot/get", [
            'bundle_id'      => $bundleId,
            'warehouse_id'   => $warehouseId,
            'interval_start' => $intervalStart,
            'interval_end'   => $intervalEnd,
        ]);
    }

    /**
     * Changes the timeslot of a draft.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDirectTimeslotEdit
     *
     * @return TTimeslotEditResponse
     */
    public function draftDirectTimeslotEdit(string $supplyId, int $rowVersion, string $timeslotStart): array
    {
        return $this->request('POST', "{$this->path}/draft/direct/timeslot/edit", [
            'supply_id'      => $supplyId,
            'row_version'    => $rowVersion,
            'timeslot_start' => $timeslotStart,
        ]);
    }

    /**
     * Creates a draft for a drop-off supply.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDropOffCreate
     *
     * @param array{bundle_id: string, warehouse_id: int, package_units_count: int, delivery_details: array{drop_off_date: string, drop_off_point_id: int, drop_off_province_uuid: string}} $requestData
     *
     * @return TCreateResponse
     */
    public function draftDropOffCreate(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/draft/drop-off/create",
            $this->pickDraftCreate($requestData, [
                'drop_off_date',
                'drop_off_point_id',
                'drop_off_province_uuid',
            ], ['drop_off_point_id' => 'int'])
        );
    }

    /**
     * Deletes a drop-off draft.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDropOffDelete
     *
     * @return TDeleteResponse
     */
    public function draftDropOffDelete(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/draft/drop-off/delete", ['supply_id' => $supplyId]);
    }

    /**
     * Changes the drop-off point and date of a draft.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDropOffDlvEdit
     *
     * @param array{supply_id: string, row_version: int, drop_off_date: string, drop_off_point_id: int, drop_off_province_uuid: string} $requestData
     *
     * @return array{row_version?: int}
     */
    public function draftDropOffDlvEdit(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'supply_id',
                'row_version',
                'drop_off_date',
                'drop_off_point_id',
                'drop_off_province_uuid',
            ]),
            [
                'supply_id'              => 'str',
                'row_version'            => 'int',
                'drop_off_date'          => 'str',
                'drop_off_point_id'      => 'int',
                'drop_off_province_uuid' => 'str',
            ]
        );

        return $this->request('POST', "{$this->path}/draft/drop-off/dlv/edit", $requestData);
    }

    /**
     * Turns a drop-off draft into a supply order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDropOffRegistrate
     *
     * @return TRegistrateResponse
     */
    public function draftDropOffRegistrate(string $supplyId, int $rowVersion): array
    {
        return $this->request('POST', "{$this->path}/draft/drop-off/registrate", [
            'supply_id'   => $supplyId,
            'row_version' => $rowVersion,
        ]);
    }

    /**
     * Provinces with drop-off points.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDropOffProvinceList
     *
     * @return array{provinces?: list<array>}
     */
    public function draftDropOffProvinceList(int $warehouseId): array
    {
        return $this->request('POST', "{$this->path}/draft/drop-off/province/list", [
            'warehouse_id' => $warehouseId,
        ]);
    }

    /**
     * Drop-off points of a province.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDropOffPointList
     *
     * @return array{drop_off_points?: list<array>}
     */
    public function draftDropOffPointList(int $warehouseId, string $provinceUuid, int $pageSize = 100, ?int $nextPageNumber = null): array
    {
        $requestData = [
            'warehouse_id'  => $warehouseId,
            'province_uuid' => $provinceUuid,
            'page_size'     => $pageSize,
        ];

        if (null !== $nextPageNumber) {
            $requestData['next_page_number'] = $nextPageNumber;
        }

        return $this->request('POST', "{$this->path}/draft/drop-off/point/list", $requestData);
    }

    /**
     * Drop-off point timetable.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDropOffPointTimetable
     *
     * @return array{calendar?: list<array>}
     */
    public function draftDropOffPointTimetable(int $warehouseId, string $provinceUuid, int $dropOffPointId): array
    {
        return $this->request('POST', "{$this->path}/draft/drop-off/point/timetable", [
            'warehouse_id'      => $warehouseId,
            'province_uuid'     => $provinceUuid,
            'drop_off_point_id' => $dropOffPointId,
        ]);
    }

    /**
     * Validates products for a drop-off supply.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftDropOffProductValidate
     *
     * @param list<TSkuItem> $skus
     *
     * @return TValidateResponse
     */
    public function draftDropOffProductValidate(int $warehouseId, array $skus): array
    {
        return $this->request(
            'POST',
            "{$this->path}/draft/drop-off/product/validate",
            $this->pickValidate($warehouseId, $skus)
        );
    }

    /**
     * Creates a draft for a supply picked up by a courier.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftPickUpCreate
     *
     * @param array{bundle_id: string, warehouse_id: int, package_units_count: int, delivery_details: array{address: string, comment: string, date: string, sender_name: string, sender_phone: string}} $requestData
     *
     * @return TCreateResponse
     */
    public function draftPickUpCreate(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/draft/pick-up/create",
            $this->pickDraftCreate($requestData, [
                'address',
                'comment',
                'date',
                'sender_name',
                'sender_phone',
            ])
        );
    }

    /**
     * Deletes a pick-up draft.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftPickUpDelete
     *
     * @return TDeleteResponse
     */
    public function draftPickUpDelete(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/draft/pick-up/delete", ['supply_id' => $supplyId]);
    }

    /**
     * Changes the pick-up details of a draft.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftPickUpDlvEdit
     *
     * @param array{supply_id: string, row_version: int, pickup_details: array{address: string, comment: string, date: string, sender_name: string, sender_phone: string}} $requestData
     *
     * @return array{row_version?: int}
     */
    public function draftPickUpDlvEdit(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, ['supply_id', 'row_version', 'pickup_details']);

        if (isset($requestData['pickup_details'])) {
            $requestData['pickup_details'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['pickup_details'], [
                    'address',
                    'comment',
                    'date',
                    'sender_name',
                    'sender_phone',
                ]),
                [
                    'address'      => 'str',
                    'comment'      => 'str',
                    'date'         => 'str',
                    'sender_name'  => 'str',
                    'sender_phone' => 'str',
                ]
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'supply_id'   => 'str',
            'row_version' => 'int',
        ]);

        return $this->request('POST', "{$this->path}/draft/pick-up/dlv/edit", $requestData);
    }

    /**
     * Validates products for a pick-up supply.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftPickUpProductValidate
     *
     * @param list<TSkuItem> $skus
     *
     * @return TValidateResponse
     */
    public function draftPickUpProductValidate(int $warehouseId, array $skus): array
    {
        return $this->request(
            'POST',
            "{$this->path}/draft/pick-up/product/validate",
            $this->pickValidate($warehouseId, $skus)
        );
    }

    /**
     * Turns a pick-up draft into a supply order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpDraftPickUpRegistrate
     *
     * @return TRegistrateResponse
     */
    public function draftPickUpRegistrate(string $supplyId, int $rowVersion): array
    {
        return $this->request('POST', "{$this->path}/draft/pick-up/registrate", [
            'supply_id'   => $supplyId,
            'row_version' => $rowVersion,
        ]);
    }

    /**
     * Supply orders list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderList
     *
     * @return TListResponse
     */
    public function orderList(int $count = 100, ?int $lastId = null): array
    {
        return $this->request('POST', "{$this->path}/order/list", $this->pagination($count, $lastId));
    }

    /**
     * Supply order info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderGet
     *
     * @return TOrder
     */
    public function orderGet(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/order/get", ['supply_id' => $supplyId]);
    }

    /**
     * Cancels an order delivered by Ozon logistics.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderDirectCancel
     *
     * @return TEditResponse
     */
    public function orderDirectCancel(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/order/direct/cancel", ['supply_id' => $supplyId]);
    }

    /**
     * Changes the driver and vehicle of an order delivered by the seller.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderDirectSellerDlvEdit
     *
     * @param array{supply_id: string, row_version: int, driver_name: string, vehicle_number: string, vehicle_type: string} $requestData
     *
     * @return TEditResponse
     */
    public function orderDirectSellerDlvEdit(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/order/direct/seller-dlv/edit",
            $this->pickSellerDlvEdit($requestData)
        );
    }

    /**
     * Changes the timeslot of an order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderDirectTimeslotEdit
     *
     * @return TTimeslotEditResponse
     */
    public function orderDirectTimeslotEdit(string $supplyId, int $rowVersion, string $timeslotStart): array
    {
        return $this->request('POST', "{$this->path}/order/direct/timeslot/edit", [
            'supply_id'      => $supplyId,
            'row_version'    => $rowVersion,
            'timeslot_start' => $timeslotStart,
        ]);
    }

    /**
     * Available timeslots for an order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderDirectTimeslotList
     *
     * @return TTimeslotListResponse
     */
    public function orderDirectTimeslotList(string $supplyId, string $intervalStart, string $intervalEnd): array
    {
        return $this->request('POST', "{$this->path}/order/direct/timeslot/list", [
            'supply_id'      => $supplyId,
            'interval_start' => $intervalStart,
            'interval_end'   => $intervalEnd,
        ]);
    }

    /**
     * Cancels a drop-off order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderDropOffCancel
     *
     * @return TEditResponse
     */
    public function orderDropOffCancel(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/order/drop-off/cancel", ['supply_id' => $supplyId]);
    }

    /**
     * Changes the drop-off date of an order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderDropOffDlvEdit
     *
     * @return array{row_version?: int}
     */
    public function orderDropOffDlvEdit(string $supplyId, int $rowVersion, string $dropOffDate): array
    {
        return $this->request('POST', "{$this->path}/order/drop-off/dlv/edit", [
            'supply_id'     => $supplyId,
            'row_version'   => $rowVersion,
            'drop_off_date' => $dropOffDate,
        ]);
    }

    /**
     * Drop-off point timetable for an order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderDropOffTimetable
     *
     * @return array{calendar?: list<array>}
     */
    public function orderDropOffTimetable(int $warehouseId, string $provinceUuid, int $dropOffPointId): array
    {
        return $this->request('POST', "{$this->path}/order/drop-off/timetable", [
            'warehouse_id'      => $warehouseId,
            'province_uuid'     => $provinceUuid,
            'drop_off_point_id' => $dropOffPointId,
        ]);
    }

    /**
     * Cancels a pick-up order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderPickUpCancel
     *
     * @return TEditResponse
     */
    public function orderPickUpCancel(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/order/pick-up/cancel", ['supply_id' => $supplyId]);
    }

    /**
     * Changes the sender of a pick-up order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpOrderPickUpDlvEdit
     *
     * @param array{supply_id: string, row_version: int, pickup_details: array{sender_name: string, sender_phone: string}} $requestData
     *
     * @return TEditResponse
     */
    public function orderPickUpDlvEdit(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, ['supply_id', 'row_version', 'pickup_details']);

        if (isset($requestData['pickup_details'])) {
            $requestData['pickup_details'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['pickup_details'], ['sender_name', 'sender_phone']),
                ['sender_name' => 'str', 'sender_phone' => 'str']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'supply_id'   => 'str',
            'row_version' => 'int',
        ]);

        return $this->request('POST', "{$this->path}/order/pick-up/dlv/edit", $requestData);
    }

    /**
     * Requests generation of the transfer act.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpActFromCreate
     *
     * @return array{is_success?: bool, file_uuid?: string, errors?: list<string>}
     */
    public function actFromCreate(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/act-from/create", ['supply_id' => $supplyId]);
    }

    /**
     * Transfer act status and download link.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpActFromGet
     *
     * @return array{status?: string, cdn_url?: string, error?: string}
     */
    public function actFromGet(string $fileUuid): array
    {
        return $this->request('POST', "{$this->path}/act-from/get", ['file_uuid' => $fileUuid]);
    }

    /**
     * Requests generation of the acceptance act.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpActToCreate
     *
     * @return array{code?: string}
     */
    public function actToCreate(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/act-to/create", ['supply_id' => $supplyId]);
    }

    /**
     * Acceptance act status and download link.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpActToGet
     *
     * @return array{state?: string, label_url?: string, error_message?: string}
     */
    public function actToGet(string $supplyId, string $code): array
    {
        return $this->request('POST', "{$this->path}/act-to/get", [
            'supply_id' => $supplyId,
            'code'      => $code,
        ]);
    }

    /**
     * Archived supply info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpArchiveGet
     *
     * @return TArchive
     */
    public function archiveGet(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/archive/get", ['supply_id' => $supplyId]);
    }

    /**
     * Archived supplies list. In the specification count and last_id are strings.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpArchiveList
     *
     * @return TListResponse
     */
    public function archiveList(int $count = 100, ?int $lastId = null): array
    {
        $requestData = ['count' => (string) $count];

        if (null !== $lastId) {
            $requestData['last_id'] = (string) $lastId;
        }

        return $this->request('POST', "{$this->path}/archive/list", $requestData);
    }

    /**
     * Requests generation of the supply labels.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpLabelCreate
     *
     * @return array{code?: string}
     */
    public function labelCreate(string $supplyId): array
    {
        return $this->request('POST', "{$this->path}/label/create", ['supply_id' => $supplyId]);
    }

    /**
     * Supply labels status and download link.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FbpLabelGet
     *
     * @return array{state?: string, label_url?: string}
     */
    public function labelGet(string $supplyId, string $code): array
    {
        return $this->request('POST', "{$this->path}/label/get", [
            'supply_id' => $supplyId,
            'code'      => $code,
        ]);
    }

    /**
     * @return array{count: int, last_id?: int}
     */
    private function pagination(int $count, ?int $lastId): array
    {
        $requestData = ['count' => $count];

        if (null !== $lastId) {
            $requestData['last_id'] = $lastId;
        }

        return $requestData;
    }

    /**
     * @param list<TSkuItem> $skus
     *
     * @return array{warehouse_id: int, skus: list<TSkuItem>}
     */
    private function pickValidate(int $warehouseId, array $skus): array
    {
        return [
            'warehouse_id' => $warehouseId,
            'skus'         => array_map(static function (array $item): array {
                /** @var TSkuItem $casted */
                $casted = TypeCaster::castArr(
                    ArrayHelper::pick($item, ['sku', 'count']),
                    ['sku' => 'int', 'count' => 'int']
                );

                return $casted;
            }, $skus),
        ];
    }

    /**
     * @param array<array-key, mixed> $requestData
     * @param list<string>            $deliveryDetailsKeys
     * @param array<string, string>   $deliveryDetailsCast
     *
     * @return array<string, mixed>
     */
    private function pickDraftCreate(array $requestData, array $deliveryDetailsKeys, array $deliveryDetailsCast = []): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'bundle_id',
            'warehouse_id',
            'package_units_count',
            'delivery_details',
        ]);

        if (isset($requestData['delivery_details'])) {
            $requestData['delivery_details'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['delivery_details'], $deliveryDetailsKeys),
                $deliveryDetailsCast
            );
        }

        return TypeCaster::castArr($requestData, [
            'bundle_id'           => 'str',
            'warehouse_id'        => 'int',
            'package_units_count' => 'int',
        ]);
    }

    /**
     * @param array<array-key, mixed> $requestData
     *
     * @return array<string, mixed>
     */
    private function pickSellerDlvEdit(array $requestData): array
    {
        return TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'supply_id',
                'row_version',
                'driver_name',
                'vehicle_number',
                'vehicle_type',
            ]),
            [
                'supply_id'      => 'str',
                'row_version'    => 'int',
                'driver_name'    => 'str',
                'vehicle_number' => 'str',
                'vehicle_type'   => 'str',
            ]
        );
    }
}
