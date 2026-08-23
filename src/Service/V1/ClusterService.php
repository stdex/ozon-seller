<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Кластеры и склады.
 *
 * Типы описаны по var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TClusterType = 'CLUSTER_TYPE_OZON'|'CLUSTER_TYPE_CIS'
 * @psalm-type TListResponse = array{
 *     clusters?: list<array{
 *         id?: int,
 *         name?: string,
 *         type?: TClusterType,
 *         macrolocal_cluster_id?: int,
 *         logistic_clusters?: list<array>
 *     }>
 * }
 */
class ClusterService extends AbstractService
{
    /**
     * Информация о кластерах и их складах.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ClusterAPI_ClusterList
     *
     * @param list<int|string> $clusterIds
     * @param TClusterType     $clusterType
     *
     * @return TListResponse
     */
    public function list(array $clusterIds = [], string $clusterType = 'CLUSTER_TYPE_OZON'): array
    {
        $requestData = ['cluster_type' => $clusterType];

        if ($clusterIds) {
            $requestData['cluster_ids'] = array_map('strval', $clusterIds);
        }

        return $this->request('POST', '/v1/cluster/list', $requestData);
    }
}
