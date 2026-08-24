<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * FBO supply drafts.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TClusterInfo = array{macrolocal_cluster_id: int, items: list<array>}
 * @psalm-type TDeliveryInfo = array{
 *     type: 'DROPOFF'|'PICKUP',
 *     drop_off_warehouse?: array,
 *     seller_warehouse_id?: int
 * }
 * @psalm-type TCreateResponse = array{draft_id?: int, errors?: list<array>}
 */
class DraftService extends AbstractService
{
    private $path = '/v1/draft';

    /**
     * Creates a direct supply draft.
     *
     * Nested `items` of the cluster info is passed as is.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DraftAPI_DraftDirectCreate
     *
     * @param TClusterInfo $clusterInfo
     * @param string       $deletionSkuMode FULL|PARTIAL
     *
     * @return TCreateResponse
     */
    public function directCreate(array $clusterInfo, string $deletionSkuMode = 'FULL'): array
    {
        return $this->request('POST', "{$this->path}/direct/create", [
            'cluster_info'      => $this->pickClusterInfo($clusterInfo),
            'deletion_sku_mode' => $deletionSkuMode,
        ]);
    }

    /**
     * Creates a crossdock supply draft.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DraftAPI_DraftCrossdockCreate
     *
     * @param TClusterInfo  $clusterInfo
     * @param TDeliveryInfo $deliveryInfo
     * @param string        $deletionSkuMode FULL|PARTIAL
     *
     * @return TCreateResponse
     */
    public function crossdockCreate(array $clusterInfo, array $deliveryInfo, string $deletionSkuMode = 'FULL'): array
    {
        return $this->request('POST', "{$this->path}/crossdock/create", [
            'cluster_info'      => $this->pickClusterInfo($clusterInfo),
            'delivery_info'     => $this->pickDeliveryInfo($deliveryInfo),
            'deletion_sku_mode' => $deletionSkuMode,
        ]);
    }

    /**
     * Creates a multi-cluster supply draft.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DraftAPI_DraftMultiClusterCreate
     *
     * @param list<TClusterInfo> $clustersInfo
     * @param TDeliveryInfo      $deliveryInfo
     * @param string             $deletionSkuMode FULL|PARTIAL
     *
     * @return TCreateResponse
     */
    public function multiClusterCreate(array $clustersInfo, array $deliveryInfo, string $deletionSkuMode = 'FULL'): array
    {
        return $this->request('POST', "{$this->path}/multi-cluster/create", [
            'clusters_info'     => array_map([$this, 'pickClusterInfo'], $clustersInfo),
            'delivery_info'     => $this->pickDeliveryInfo($deliveryInfo),
            'deletion_sku_mode' => $deletionSkuMode,
        ]);
    }

    /**
     * @param array<array-key, mixed> $clusterInfo
     *
     * @return array<string, mixed>
     */
    private function pickClusterInfo(array $clusterInfo): array
    {
        return TypeCaster::castArr(
            ArrayHelper::pick($clusterInfo, ['macrolocal_cluster_id', 'items']),
            ['macrolocal_cluster_id' => 'int']
        );
    }

    /**
     * @param array<array-key, mixed> $deliveryInfo
     *
     * @return array<string, mixed>
     */
    private function pickDeliveryInfo(array $deliveryInfo): array
    {
        return TypeCaster::castArr(
            ArrayHelper::pick($deliveryInfo, ['type', 'drop_off_warehouse', 'seller_warehouse_id']),
            ['type' => 'str', 'seller_warehouse_id' => 'int']
        );
    }
}
