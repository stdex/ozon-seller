<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class ActionsService extends AbstractService
{
    /**
     * Discount requests list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/promos_task_list_v2
     *
     * @param string $status ALL|NEW|APPROVED|DECLINED
     *
     * @return array{tasks?: list<array>}
     */
    public function discountsTaskList(string $status = 'NEW', int $limit = 100, ?int $lastId = null): array
    {
        $requestData = [
            'status' => $status,
            'limit'  => $limit,
        ];

        if (null !== $lastId) {
            $requestData['last_id'] = $lastId;
        }

        return $this->request('POST', '/v2/actions/discounts-task/list', $requestData);
    }
}
