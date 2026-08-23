<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

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
 *     provider_id?: int,
 *     status?: string,
 *     warehouse_id?: int
 * }
 * @psalm-type TListRequest = array{
 *     filter?: TListFilter,
 *     limit?: int,
 *     offset?: int
 * }
 * @psalm-type TListResponse = array{
 *     result?: list<array>,
 *     has_next?: bool
 * }
 * @psalm-type TReturnSettingsResponse = array{
 *     settings?: array{
 *         post_office_zipcode?: string,
 *         courier_details?: array,
 *         transport_company_details?: array
 *     }
 * }
 */
class DeliveryMethodService extends AbstractService
{
    private $path = '/v1/delivery-method';

    /**
     * Delivery methods list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_DeliveryMethodList
     *
     * @param TListRequest $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 50, 'offset' => 0],
            ArrayHelper::pick($requestData, ['filter', 'limit', 'offset'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['provider_id', 'status', 'warehouse_id']),
                ['provider_id' => 'int', 'status' => 'str', 'warehouse_id' => 'int']
            );
        }

        $requestData = TypeCaster::castArr($requestData, ['limit' => 'int', 'offset' => 'int']);

        return $this->request('POST', "{$this->path}/list", $requestData, true, false);
    }

    /**
     * Return settings of a delivery method.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/WarehouseAPI_DeliveryMethodReturnSettingsGet
     *
     * @return TReturnSettingsResponse
     */
    public function returnSettingsGet(int $deliveryMethodId): array
    {
        return $this->request('POST', "{$this->path}/return/settings/get", [
            'delivery_method_id' => $deliveryMethodId,
        ]);
    }
}
