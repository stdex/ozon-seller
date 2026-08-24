<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * FBO supply orders.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class SupplyOrderService extends AbstractService
{
    /**
     * Available supply timeslots and the limits on changing them.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplyOrderAPI_SupplyOrderTimeslotListV2
     *
     * @return array{timeslots_info?: array, limit_exceeded?: array, timeslot_change_forbidden?: array}
     */
    public function timeslotList(int $orderId): array
    {
        return $this->request('POST', '/v2/supply-order/timeslot/list', ['order_id' => $orderId]);
    }
}
