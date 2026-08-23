<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1\Posting;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * FBP postings.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TPosting = array{
 *     posting_number?: string,
 *     order_id?: int,
 *     order_number?: string,
 *     order_date?: string,
 *     status?: int,
 *     substatus?: string,
 *     in_process_at?: string,
 *     tpl_provider_id?: int,
 *     products?: list<array>,
 *     analytics_data?: array,
 *     financial_data?: array,
 *     cancellation?: array
 * }
 * @psalm-type TListFilter = array{
 *     since?: string,
 *     to?: string,
 *     statuses?: list<string>,
 *     posting_numbers?: list<string>,
 *     name?: string,
 *     offer_id?: string
 * }
 * @psalm-type TListRequest = array{
 *     filter?: TListFilter,
 *     cursor?: string,
 *     limit?: int,
 *     sort_by?: string,
 *     sort_dir?: 'ASC'|'DESC'
 * }
 * @psalm-type TListResponse = array{
 *     postings?: list<TPosting>,
 *     cursor?: string
 * }
 */
class FbpService extends AbstractService
{
    private $path = '/v1/posting/fbp';

    /**
     * Posting info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetFbpPosting
     *
     * @return array{posting?: TPosting}
     */
    public function get(string $postingNumber): array
    {
        return $this->request('POST', "{$this->path}/get", ['posting_number' => $postingNumber]);
    }

    /**
     * Postings list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetFbpPostingList
     *
     * @param TListRequest $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 10, 'sort_dir' => 'ASC'],
            ArrayHelper::pick($requestData, ['filter', 'cursor', 'limit', 'sort_by', 'sort_dir'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], [
                    'since',
                    'to',
                    'statuses',
                    'posting_numbers',
                    'name',
                    'offer_id',
                ]),
                [
                    'since'           => 'str',
                    'to'              => 'str',
                    'statuses'        => 'arrOfStr',
                    'posting_numbers' => 'arrOfStr',
                    'name'            => 'str',
                    'offer_id'        => 'str',
                ]
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'cursor'   => 'str',
            'limit'    => 'int',
            'sort_by'  => 'str',
            'sort_dir' => 'str',
        ]);

        return $this->request('POST', "{$this->path}/list", $requestData);
    }
}
