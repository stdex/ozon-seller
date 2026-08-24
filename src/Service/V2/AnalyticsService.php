<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * @psalm-type TStockOnWarehouseRow = array{
 *     sku?: int,
 *     item_code?: string,
 *     item_name?: string,
 *     free_to_sell_amount?: int,
 *     promised_amount?: int,
 *     reserved_amount?: int,
 *     warehouse_name?: string
 * }
 */
class AnalyticsService extends AbstractService
{
    private $path = '/v2/analytics';

    /**
     * Method for getting a report on leftover stocks and products movement at Ozon warehouses.
     *
     * @see https://docs.ozon.ru/api/seller/en/#operation/AnalyticsAPI_AnalyticsGetStockOnWarehousesV2
     *
     * @param 'ALL'|'EXPRESS_DARK_STORE'|'NOT_EXPRESS_DARK_STORE' $warehouse_type
     *
     * @return array{rows?: list<TStockOnWarehouseRow>}
     */
    public function stockOnWarehouses(int $offset = 0, int $limit = 10, $warehouse_type = "ALL"): array
    {
        $body = [
            'offset' => $offset,
            'limit'  => $limit,
            'warehouse_type' => $warehouse_type
        ];

        return $this->request(
            'POST',
            "{$this->path}/stock_on_warehouses",
            $body
        );
    }

}
