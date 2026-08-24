<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * FBS delivery polygons.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class PolygonService extends AbstractService
{
    /**
     * Binds a delivery method to a delivery polygon.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PolygonAPI_PolygonBindV2
     *
     * @param int $time delivery time in minutes
     */
    public function bind(int $polygonId, int $deliveryMethodId, int $warehouseId, int $time): array
    {
        return $this->request('POST', '/v2/polygon/bind', [
            'polygon_id'         => $polygonId,
            'delivery_method_id' => $deliveryMethodId,
            'warehouse_id'       => $warehouseId,
            'time'               => $time,
        ]);
    }
}
