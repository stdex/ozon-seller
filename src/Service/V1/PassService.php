<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Arrival passes of carriages and return giveouts.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TListFilter = array{
 *     arrival_pass_ids?: list<string>,
 *     arrival_reason?: string,
 *     dropoff_point_ids?: list<string>,
 *     warehouse_ids?: list<string>,
 *     only_active_passes?: bool
 * }
 * @psalm-type TListRequest = array{filter?: TListFilter, cursor?: string, limit?: int}
 * @psalm-type TListResponse = array{arrival_passes?: list<array>, cursor?: string}
 */
class PassService extends AbstractService
{
    /**
     * Arrival passes list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PassAPI_PassList
     *
     * @param TListRequest $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($requestData, ['filter', 'cursor', 'limit'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], [
                    'arrival_pass_ids',
                    'arrival_reason',
                    'dropoff_point_ids',
                    'warehouse_ids',
                    'only_active_passes',
                ]),
                [
                    'arrival_pass_ids'   => 'arrOfStr',
                    'arrival_reason'     => 'str',
                    'dropoff_point_ids'  => 'arrOfStr',
                    'warehouse_ids'      => 'arrOfStr',
                    'only_active_passes' => 'bool',
                ]
            );
        }

        $requestData = TypeCaster::castArr($requestData, ['cursor' => 'str', 'limit' => 'int']);

        return $this->request('POST', '/v1/pass/list', $requestData);
    }
}
