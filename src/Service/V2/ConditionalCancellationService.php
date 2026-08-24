<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Cancellation requests that need the seller's approval.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TListFilters = array{
 *     cancellation_initiator?: list<string>,
 *     posting_number?: list<string>,
 *     state?: 'ALL'|'ON_APPROVAL'|'APPROVED'|'REJECTED'
 * }
 * @psalm-type TListRequest = array{
 *     filters?: TListFilters,
 *     with?: array{counter?: bool},
 *     limit?: int,
 *     last_id?: int
 * }
 * @psalm-type TListResponse = array{result?: list<array>, counter?: int, last_id?: int}
 */
class ConditionalCancellationService extends AbstractService
{
    private $path = '/v2/conditional-cancellation';

    /**
     * Cancellation requests list. The response has a top-level `result` key,
     * so it is returned unwrapped.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ConditionalCancellationAPI_ConditionalCancellationListV2
     *
     * @param TListRequest $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($requestData, ['filters', 'with', 'limit', 'last_id'])
        );

        if (isset($requestData['filters'])) {
            $requestData['filters'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filters'], [
                    'cancellation_initiator',
                    'posting_number',
                    'state',
                ]),
                [
                    'cancellation_initiator' => 'arrOfStr',
                    'posting_number'         => 'arrOfStr',
                    'state'                  => 'str',
                ]
            );
        }

        if (isset($requestData['with'])) {
            $requestData['with'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['with'], ['counter']),
                ['counter' => 'bool']
            );
        }

        $requestData = TypeCaster::castArr($requestData, ['limit' => 'int', 'last_id' => 'int']);

        return $this->request('POST', "{$this->path}/list", $requestData, true, false);
    }

    /**
     * Approves a cancellation request.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ConditionalCancellationAPI_ConditionalCancellationApproveV2
     */
    public function approve(int $cancellationId, string $comment = ''): array
    {
        return $this->request('POST', "{$this->path}/approve", $this->move($cancellationId, $comment));
    }

    /**
     * Rejects a cancellation request.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ConditionalCancellationAPI_ConditionalCancellationRejectV2
     */
    public function reject(int $cancellationId, string $comment = ''): array
    {
        return $this->request('POST', "{$this->path}/reject", $this->move($cancellationId, $comment));
    }

    /**
     * @return array{cancellation_id: int, comment?: string}
     */
    private function move(int $cancellationId, string $comment): array
    {
        $requestData = ['cancellation_id' => $cancellationId];

        if ('' !== $comment) {
            $requestData['comment'] = $comment;
        }

        return $requestData;
    }
}
