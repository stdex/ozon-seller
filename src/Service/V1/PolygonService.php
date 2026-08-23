<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * FBS delivery polygons.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TBindRequest = array{
 *     delivery_method_id: int,
 *     warehouse_location: array{lat: string, lon: string},
 *     polygons: list<array{polygon_id: int, time: int}>
 * }
 * @psalm-type TListResponse = array{
 *     polygons?: list<array{polygon_id?: int, coordinates?: string, time?: int}>
 * }
 */
class PolygonService extends AbstractService
{
    private $path = '/v1/polygon';

    /**
     * Creates a delivery polygon.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PolygonAPI_PolygonCreate
     *
     * @param string $coordinates JSON string with the polygon coordinates
     *
     * @return array{polygon_id?: int}
     */
    public function create(string $coordinates): array
    {
        return $this->request('POST', "{$this->path}/create", ['coordinates' => $coordinates]);
    }

    /**
     * Binds a delivery method to delivery polygons.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PolygonAPI_PolygonBind
     *
     * @param TBindRequest $requestData
     */
    public function bind(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'delivery_method_id',
            'warehouse_location',
            'polygons',
        ]);

        if (isset($requestData['warehouse_location'])) {
            $requestData['warehouse_location'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['warehouse_location'], ['lat', 'lon']),
                ['lat' => 'str', 'lon' => 'str']
            );
        }

        if (isset($requestData['polygons'])) {
            $requestData['polygons'] = array_map(static function (array $polygon): array {
                return TypeCaster::castArr(
                    ArrayHelper::pick($polygon, ['polygon_id', 'time']),
                    ['polygon_id' => 'int', 'time' => 'int']
                );
            }, $requestData['polygons']);
        }

        $requestData = TypeCaster::castArr($requestData, ['delivery_method_id' => 'int']);

        return $this->request('POST', "{$this->path}/bind", $requestData);
    }

    /**
     * Delivery method polygons list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PolygonAPI_PolygonList
     *
     * @return TListResponse
     */
    public function list(int $deliveryMethodId, int $warehouseId): array
    {
        return $this->request('POST', "{$this->path}/list", [
            'delivery_method_id' => $deliveryMethodId,
            'warehouse_id'       => $warehouseId,
        ]);
    }

    /**
     * Deletes a polygon.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PolygonAPI_PolygonDelete
     */
    public function delete(int $polygonId, int $deliveryMethodId, int $warehouseId): array
    {
        return $this->request('POST', "{$this->path}/delete", [
            'polygon_id'         => $polygonId,
            'delivery_method_id' => $deliveryMethodId,
            'warehouse_id'       => $warehouseId,
        ]);
    }

    /**
     * Changes the polygon coordinates.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PolygonAPI_PolygonTimeCoordinatesUpdate
     *
     * @param string $coordinates JSON string with the polygon coordinates
     */
    public function timeCoordinatesUpdate(int $polygonId, int $deliveryMethodId, int $warehouseId, string $coordinates): array
    {
        return $this->request('POST', "{$this->path}/time/coordinates/update", [
            'polygon_id'         => $polygonId,
            'delivery_method_id' => $deliveryMethodId,
            'warehouse_id'       => $warehouseId,
            'coordinates'        => $coordinates,
        ]);
    }

    /**
     * Changes the delivery time for a polygon.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PolygonAPI_PolygonTimeSet
     */
    public function timeSet(int $polygonId, int $deliveryMethodId, int $warehouseId, int $currentTime, int $newTime): array
    {
        return $this->request('POST', "{$this->path}/time/set", [
            'polygon_id'         => $polygonId,
            'delivery_method_id' => $deliveryMethodId,
            'warehouse_id'       => $warehouseId,
            'current_time'       => $currentTime,
            'new_time'           => $newTime,
        ]);
    }
}
