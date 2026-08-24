<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2\Posting;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Postings with digital products.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TListFilter = array{
 *     since?: string,
 *     to?: string,
 *     order_numbers?: list<string>,
 *     posting_numbers?: list<string>
 * }
 * @psalm-type TListWith = array{
 *     analytics_data?: bool,
 *     financial_data?: bool,
 *     legal_info?: bool
 * }
 * @psalm-type TListRequest = array{
 *     filter?: TListFilter,
 *     with?: TListWith,
 *     cursor?: string,
 *     limit?: int,
 *     sort_dir?: 'ASC'|'DESC'
 * }
 * @psalm-type TListResponse = array{
 *     postings?: list<array>,
 *     cursor?: string,
 *     has_next?: bool
 * }
 */
class DigitalService extends AbstractService
{
    /**
     * Postings with digital products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingDigitalListV2
     *
     * @param TListRequest $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 100, 'sort_dir' => 'ASC'],
            ArrayHelper::pick($requestData, ['filter', 'with', 'cursor', 'limit', 'sort_dir'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['since', 'to', 'order_numbers', 'posting_numbers']),
                [
                    'since'           => 'str',
                    'to'              => 'str',
                    'order_numbers'   => 'arrOfStr',
                    'posting_numbers' => 'arrOfStr',
                ]
            );
        }

        if (isset($requestData['with'])) {
            $requestData['with'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['with'], ['analytics_data', 'financial_data', 'legal_info']),
                ['analytics_data' => 'bool', 'financial_data' => 'bool', 'legal_info' => 'bool']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'cursor'   => 'str',
            'limit'    => 'int',
            'sort_dir' => 'str',
        ]);

        return $this->request('POST', '/v2/posting/digital/list', $requestData);
    }
}
