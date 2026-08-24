<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * FBO supply orders.
 *
 * Many methods are asynchronous: they return an `operation_id` whose result is
 * fetched by the matching status method.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TBundleRequest = array{
 *     bundle_ids: list<string>,
 *     limit?: int,
 *     last_id?: string,
 *     query?: string,
 *     is_asc?: bool,
 *     sort_field?: 'SKU'|'NAME'|'QUANTITY'|'TOTAL_VOLUME_IN_LITRES',
 *     item_tags_calculation?: array{dropoff_warehouse_id: string, storage_warehouse_ids: list<string>}
 * }
 * @psalm-type TBundleResponse = array{
 *     items?: list<array>,
 *     total_count?: int,
 *     has_next?: bool,
 *     last_id?: string
 * }
 * @psalm-type TDetails = array{
 *     order_id?: int,
 *     order_number?: string,
 *     state?: string,
 *     state_updated_date?: string,
 *     created_date?: string,
 *     data_filling_deadline_utc?: string,
 *     dropoff_warehouse_id?: int,
 *     order_tags?: array,
 *     supplies?: list<array>,
 *     timeslot?: array,
 *     vehicle?: array
 * }
 * @psalm-type TVehicle = array{
 *     driver_name: string,
 *     driver_phone: string,
 *     vehicle_model: string,
 *     vehicle_number: string
 * }
 * @psalm-type TContentItem = array{sku: int, quantity: int, quant: int}
 */
class SupplyOrderService extends AbstractService
{
    private $path = '/v1/supply-order';

    /**
     * Number of supply orders by status.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderStatusCounter
     *
     * @return array{items?: list<array>}
     */
    public function statusCounter(): array
    {
        return $this->request('POST', "{$this->path}/status/counter", '{}');
    }

    /**
     * Products of supply order bundles.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderBundle
     *
     * @param TBundleRequest $requestData
     *
     * @return TBundleResponse
     */
    public function bundle(array $requestData): array
    {
        $requestData = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($requestData, [
                'bundle_ids',
                'limit',
                'last_id',
                'query',
                'is_asc',
                'sort_field',
                'item_tags_calculation',
            ])
        );

        if (isset($requestData['item_tags_calculation'])) {
            $requestData['item_tags_calculation'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['item_tags_calculation'], [
                    'dropoff_warehouse_id',
                    'storage_warehouse_ids',
                ]),
                ['dropoff_warehouse_id' => 'str', 'storage_warehouse_ids' => 'arrOfStr']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'bundle_ids' => 'arrOfStr',
            'limit'      => 'int',
            'last_id'    => 'str',
            'query'      => 'str',
            'is_asc'     => 'bool',
            'sort_field' => 'str',
        ]);

        return $this->request('POST', "{$this->path}/bundle", $requestData);
    }

    /**
     * Supply order details.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderDetails
     *
     * @return TDetails
     */
    public function details(int $orderId): array
    {
        return $this->request('POST', "{$this->path}/details", ['order_id' => $orderId]);
    }

    /**
     * Cancels a supply order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderCancel
     *
     * @return array{operation_id?: string}
     */
    public function cancel(int $orderId): array
    {
        return $this->request('POST', "{$this->path}/cancel", ['order_id' => $orderId]);
    }

    /**
     * Status of a supply order cancellation. The response has a top-level `result` key,
     * so it is returned unwrapped.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderCancelStatus
     *
     * @return array{status?: 'SUCCESS'|'IN_PROGRESS'|'ERROR', result?: array, error_reasons?: list<string>}
     */
    public function cancelStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/cancel/status", ['operation_id' => $operationId], true, false);
    }

    /**
     * Available supply timeslots.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderTimeslotGet
     *
     * @return array{timeslots?: list<array>, timezone?: list<array>}
     */
    public function timeslotGet(int $supplyOrderId): array
    {
        return $this->request('POST', "{$this->path}/timeslot/get", ['supply_order_id' => $supplyOrderId]);
    }

    /**
     * Sets the supply timeslot.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderTimeslotUpdate
     *
     * @return array{operation_id?: string, errors?: list<string>}
     */
    public function timeslotUpdate(int $supplyOrderId, string $from, string $to): array
    {
        return $this->request('POST', "{$this->path}/timeslot/update", [
            'supply_order_id' => $supplyOrderId,
            'timeslot'        => [
                'from' => $from,
                'to'   => $to,
            ],
        ]);
    }

    /**
     * Status of a supply timeslot update.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderTimeslotStatus
     *
     * @return array{status?: string, errors?: list<string>}
     */
    public function timeslotStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/timeslot/status", ['operation_id' => $operationId]);
    }

    /**
     * Sets the vehicle of a supply order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderPassCreate
     *
     * @param TVehicle $vehicle
     *
     * @return array{operation_id?: string, error_reasons?: list<string>}
     */
    public function passCreate(int $supplyOrderId, array $vehicle): array
    {
        $vehicle = TypeCaster::castArr(
            ArrayHelper::pick($vehicle, ['driver_name', 'driver_phone', 'vehicle_model', 'vehicle_number']),
            [
                'driver_name'    => 'str',
                'driver_phone'   => 'str',
                'vehicle_model'  => 'str',
                'vehicle_number' => 'str',
            ]
        );

        return $this->request('POST', "{$this->path}/pass/create", [
            'supply_order_id' => $supplyOrderId,
            'vehicle'         => $vehicle,
        ]);
    }

    /**
     * Status of a vehicle update. The response has a top-level `result` key,
     * so it is returned unwrapped.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderPassStatus
     *
     * @return array{result?: 'Unknown'|'Success'|'InProgress'|'Failed', errors?: list<string>}
     */
    public function passStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/pass/status", ['operation_id' => $operationId], true, false);
    }

    /**
     * Changes the content of a supply.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderContentUpdate
     *
     * @param list<TContentItem> $items
     *
     * @return array{operation_id?: string, errors?: list<string>}
     */
    public function contentUpdate(int $orderId, int $supplyId, array $items): array
    {
        $items = array_map(static function (array $item): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($item, ['sku', 'quantity', 'quant']),
                ['sku' => 'int', 'quantity' => 'int', 'quant' => 'int']
            );
        }, $items);

        return $this->request('POST', "{$this->path}/content/update", [
            'order_id'  => $orderId,
            'supply_id' => $supplyId,
            'items'     => $items,
        ]);
    }

    /**
     * Status of a supply content update.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderContentUpdateStatus
     *
     * @return array{status?: 'SUCCESS'|'IN_PROGRESS'|'ERROR', new_bundle_id?: string, errors?: list<string>}
     */
    public function contentUpdateStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/content/update/status", ['operation_id' => $operationId]);
    }

    /**
     * Validation result of a changed supply content.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderContentUpdateValidation
     *
     * @return array{validated_assortment?: array, editing_errors?: list<string>}
     */
    public function contentUpdateValidation(int $supplyId, string $newBundleId): array
    {
        return $this->request('POST', "{$this->path}/content/update/validation", [
            'supply_id'     => $supplyId,
            'new_bundle_id' => $newBundleId,
        ]);
    }

    /**
     * Acceptance acts of the supplies of an order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderActSummaryGet
     *
     * @return array{supplies_acts?: list<array>}
     */
    public function actSummaryGet(int $orderId): array
    {
        return $this->request('POST', "{$this->path}/act/summary/get", ['order_id' => $orderId]);
    }

    /**
     * Products of the acceptance acts of a supply.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderActProductGet
     *
     * @return array{supply_id?: int, supply_acts?: list<array>, skus_defects?: list<array>}
     */
    public function actProductGet(int $supplyId): array
    {
        return $this->request('POST', "{$this->path}/act/product/get", ['supply_id' => $supplyId]);
    }

    /**
     * Accepts an acceptance act.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderActAccept
     *
     * @return array{operation_id?: string, error_reasons?: list<string>}
     */
    public function actAccept(int $actId): array
    {
        return $this->request('POST', "{$this->path}/act/accept", ['act_id' => $actId]);
    }

    /**
     * Status of an acceptance act acceptance.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderActAcceptStatus
     *
     * @return array{status?: 'SUCCESS'|'IN_PROGRESS'|'FAILED', error_message?: string}
     */
    public function actAcceptStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/act/accept/status", ['operation_id' => $operationId]);
    }
}
