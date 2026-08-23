<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * @psalm-type TWarehouse = array{
 *     warehouse_id?: int,
 *     name?: string,
 *     status?: string,
 *     first_mile_type?: array{
 *         dropoff_point_id?: string,
 *         dropoff_timeslot_id?: int,
 *         first_mile_is_changing?: bool,
 *         first_mile_type?: 'DropOff'|'Pickup'
 *     },
 *     has_entrusted_acceptance?: bool,
 *     has_postings_limit?: bool,
 *     is_rfbs?: bool,
 *     is_karantin?: bool,
 *     is_kgt?: bool,
 *     is_economy?: bool,
 *     is_able_to_set_price?: bool,
 *     is_presorted?: bool,
 *     is_timetable_editable?: bool,
 *     can_print_act_in_advance?: bool,
 *     min_postings_limit?: int,
 *     postings_limit?: int,
 *     min_working_days?: int,
 *     working_days?: list<string>
 * }
 */
class WarehouseService extends AbstractService
{
    private $path = '/v1/warehouse';

    /**
     * @return list<TWarehouse>
     *@deprecated use V2\WarehouseService::list
     *
     */
    public function list(): array
    {
        return $this->request('POST', "{$this->path}/list");
    }
}
