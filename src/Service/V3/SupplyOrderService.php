<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V3;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * FBO supply orders.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TListFilter = array{
 *     states: list<string>,
 *     dropoff_warehouse_ids?: list<string>,
 *     order_number_search?: string,
 *     timeslot_from_range?: array{from?: string, to?: string, timeslot_filter_type?: string}
 * }
 * @psalm-type TListRequest = array{
 *     filter: TListFilter,
 *     limit?: int,
 *     last_id?: string,
 *     sort_by?: 'ORDER_CREATION'|'ORDER_STATE_UPDATED_AT'|'TIMESLOT_FROM_UTC'|'TIMESLOT_FROM_LOCAL',
 *     sort_dir?: 'ASC'|'DESC'
 * }
 * @psalm-type TListResponse = array{order_ids?: list<string>, last_id?: string}
 */
class SupplyOrderService extends AbstractService
{
    private $path = '/v3/supply-order';

    /**
     * Supply orders list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderListV3
     *
     * @param TListRequest $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData): array
    {
        $requestData = array_merge(
            ['limit' => 100, 'sort_by' => 'ORDER_CREATION', 'sort_dir' => 'ASC'],
            ArrayHelper::pick($requestData, ['filter', 'limit', 'last_id', 'sort_by', 'sort_dir'])
        );

        if (isset($requestData['filter'])) {
            $filter = ArrayHelper::pick($requestData['filter'], [
                'states',
                'dropoff_warehouse_ids',
                'order_number_search',
                'timeslot_from_range',
            ]);

            if (isset($filter['timeslot_from_range'])) {
                $filter['timeslot_from_range'] = ArrayHelper::pick(
                    $filter['timeslot_from_range'],
                    ['from', 'to', 'timeslot_filter_type']
                );
            }

            $requestData['filter'] = TypeCaster::castArr($filter, [
                'states'                => 'arrOfStr',
                'dropoff_warehouse_ids' => 'arrOfStr',
                'order_number_search'   => 'str',
            ]);
        }

        $requestData = TypeCaster::castArr($requestData, [
            'limit'    => 'int',
            'last_id'  => 'str',
            'sort_by'  => 'str',
            'sort_dir' => 'str',
        ]);

        return $this->request('POST', "{$this->path}/list", $requestData);
    }

    /**
     * Supply orders info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderGetV3
     *
     * @param list<int|string> $orderIds
     *
     * @return array{orders?: list<array>}
     */
    public function get(array $orderIds): array
    {
        return $this->request('POST', "{$this->path}/get", [
            'order_ids' => array_map('strval', $orderIds),
        ]);
    }
}
