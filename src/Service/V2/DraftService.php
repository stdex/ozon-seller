<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

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
 * @psalm-type TSupplyType = 'CROSSDOCK'|'DIRECT'|'MULTI_CLUSTER'
 */
class DraftService extends AbstractService
{
    private $path = '/v2/draft';

    /**
     * Draft creation result.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DraftAPI_DraftCreateInfoV2
     *
     * @return array{status?: 'UNSPECIFIED'|'SUCCESS'|'IN_PROGRESS'|'FAILED', clusters?: list<array>, errors?: list<array>}
     */
    public function createInfo(int $draftId): array
    {
        return $this->request('POST', "{$this->path}/create/info", ['draft_id' => $draftId]);
    }

    /**
     * Available supply timeslots of a draft. The response has a top-level `result` key,
     * so it is returned unwrapped.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DraftAPI_DraftTimeslotInfoV2
     *
     * @param array{draft_id: int, date_from: string, date_to: string, supply_type: TSupplyType, selected_cluster_warehouses: list<array>} $requestData
     *
     * @return array{result?: array, error_reason?: string}
     */
    public function timeslotInfo(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'draft_id',
                'date_from',
                'date_to',
                'supply_type',
                'selected_cluster_warehouses',
            ]),
            [
                'draft_id'    => 'int',
                'date_from'   => 'str',
                'date_to'     => 'str',
                'supply_type' => 'str',
            ]
        );

        return $this->request('POST', "{$this->path}/timeslot/info", $requestData, true, false);
    }

    /**
     * Turns a draft into a supply order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DraftAPI_DraftSupplyCreateV2
     *
     * @param array{draft_id: int, supply_type: TSupplyType, selected_cluster_warehouses: list<array>, timeslot?: array{from_in_timezone?: string, to_in_timezone?: string}} $requestData
     *
     * @return array{draft_id?: int, error_reasons?: list<string>}
     */
    public function supplyCreate(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'draft_id',
            'supply_type',
            'selected_cluster_warehouses',
            'timeslot',
        ]);

        if (isset($requestData['timeslot'])) {
            $requestData['timeslot'] = ArrayHelper::pick(
                $requestData['timeslot'],
                ['from_in_timezone', 'to_in_timezone']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'draft_id'    => 'int',
            'supply_type' => 'str',
        ]);

        return $this->request('POST', "{$this->path}/supply/create", $requestData);
    }

    /**
     * Status of a supply order creation.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DraftAPI_DraftSupplyCreateStatusV2
     *
     * @return array{status?: 'UNSPECIFIED'|'SUCCESS'|'IN_PROGRESS'|'FAILED', order_id?: int, error_reasons?: list<string>}
     */
    public function supplyCreateStatus(int $draftId): array
    {
        return $this->request('POST', "{$this->path}/supply/create/status", ['draft_id' => $draftId]);
    }
}
