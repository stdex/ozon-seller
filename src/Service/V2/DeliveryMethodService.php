<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Delivery methods of FBS warehouses.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TListFilter = array{
 *     delivery_method_ids?: list<int>,
 *     provider_ids?: list<int>,
 *     status?: list<string>,
 *     warehouse_ids?: list<int>
 * }
 * @psalm-type TListRequest = array{
 *     filter?: TListFilter,
 *     cursor?: string,
 *     limit?: int,
 *     sort_dir?: 'ASC'|'DESC'
 * }
 * @psalm-type TListResponse = array{
 *     delivery_methods?: list<array>,
 *     cursor?: string,
 *     has_next?: bool
 * }
 */
class DeliveryMethodService extends AbstractService
{
    /**
     * Delivery methods list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_DeliveryMethodListV2
     *
     * @param TListRequest $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 50, 'sort_dir' => 'ASC'],
            ArrayHelper::pick($requestData, ['filter', 'cursor', 'limit', 'sort_dir'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], [
                    'delivery_method_ids',
                    'provider_ids',
                    'status',
                    'warehouse_ids',
                ]),
                [
                    'delivery_method_ids' => 'arrOfInt',
                    'provider_ids'        => 'arrOfInt',
                    'status'              => 'arrOfStr',
                    'warehouse_ids'       => 'arrOfInt',
                ]
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'cursor'   => 'str',
            'limit'    => 'int',
            'sort_dir' => 'str',
        ]);

        return $this->request('POST', '/v2/delivery-method/list', $requestData);
    }
}
