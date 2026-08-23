<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Clusters and warehouses.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TCluster = array{
 *     macrolocal_cluster_id?: int,
 *     data?: array{macrolocal_cluster?: array, fulfillments?: list<array>}
 * }
 */
class ClusterService extends AbstractService
{
    /**
     * Clusters and their warehouses info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ClusterAPI_ClusterListV2
     *
     * @return list<TCluster>
     */
    public function list(): array
    {
        return $this->request('POST', '/v2/cluster/list', '{}');
    }
}
