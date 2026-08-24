<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * FBS carriages.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TDeliveryListRequest = array{
 *     filter?: array{delivery_method_id?: int, departure_date?: string},
 *     cursor?: string,
 *     limit?: int
 * }
 * @psalm-type TDeliveryListResponse = array{
 *     methods?: list<array>,
 *     cursor?: string,
 *     has_next?: bool
 * }
 */
class CarriageService extends AbstractService
{
    /**
     * Delivery methods with their carriages.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriageDeliveryListV2
     *
     * @param TDeliveryListRequest $requestData
     *
     * @return TDeliveryListResponse
     */
    public function deliveryList(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($requestData, ['filter', 'cursor', 'limit'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['delivery_method_id', 'departure_date']),
                ['delivery_method_id' => 'int', 'departure_date' => 'str']
            );
        }

        $requestData = TypeCaster::castArr($requestData, ['cursor' => 'str', 'limit' => 'int']);

        return $this->request('POST', '/v2/carriage/delivery/list', $requestData);
    }
}
