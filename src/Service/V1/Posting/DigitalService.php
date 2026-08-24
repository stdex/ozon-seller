<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1\Posting;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Postings with digital products.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TListRequest = array{
 *     filter?: array{since?: string, to?: string, posting_number?: list<string>},
 *     with?: array{analytics_data?: bool, financial_data?: bool, legal_info?: bool},
 *     dir?: 'ASC'|'DESC',
 *     limit?: int,
 *     offset?: int
 * }
 */
class DigitalService extends AbstractService
{
    /**
     * Postings with digital products.
     *
     * @deprecated use \Gam6itko\OzonSeller\Service\V2\Posting\DigitalService::list
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingDigitalList
     *
     * @param TListRequest $requestData
     *
     * @return list<array>
     */
    public function list(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 100, 'offset' => 0, 'dir' => 'ASC'],
            ArrayHelper::pick($requestData, ['filter', 'with', 'dir', 'limit', 'offset'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['since', 'to', 'posting_number']),
                ['since' => 'str', 'to' => 'str', 'posting_number' => 'arrOfStr']
            );
        }

        if (isset($requestData['with'])) {
            $requestData['with'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['with'], ['analytics_data', 'financial_data', 'legal_info']),
                ['analytics_data' => 'bool', 'financial_data' => 'bool', 'legal_info' => 'bool']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'dir'    => 'str',
            'limit'  => 'int',
            'offset' => 'int',
        ]);

        return $this->request('POST', '/v1/posting/digital/list', $requestData);
    }
}
