<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Enum\PostingScheme;
use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

class ReturnsService extends AbstractService
{
    private $path = '/v2/returns';

    /**
     * @param string $postingScheme Value from ['fbo', 'fbs']
     * @param array  $requestData   ['filter' => array, 'offset' => int, 'limit' => int]
     */
    public function company(string $postingScheme, array $requestData): array
    {
        $postingScheme = strtolower($postingScheme);
        if (!in_array($postingScheme, [PostingScheme::FBO, PostingScheme::FBS])) {
            throw new \LogicException("Unsupported posting scheme: $postingScheme");
        }

        $default = [
            'filter' => [],
            'offset' => 0,
            'limit'  => 10,
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        return $this->request('POST', "{$this->path}/company/{$postingScheme}", $requestData);
    }

    /**
     * rFBS returns list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ReturnsAPI_ReturnsRfbsListV2
     *
     * @param array{filter?: array{offer_id?: string, posting_number?: string, group_state?: list<string>, created_at?: array}, limit?: int, last_id?: int} $requestData
     *
     * @return array{returns?: array}
     */
    public function rfbsList(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($requestData, ['filter', 'limit', 'last_id'])
        );

        if (isset($requestData['filter'])) {
            $filter = ArrayHelper::pick($requestData['filter'], [
                'offer_id',
                'posting_number',
                'group_state',
                'created_at',
            ]);

            if (isset($filter['created_at'])) {
                $filter['created_at'] = ArrayHelper::pick($filter['created_at'], ['from', 'to']);
            }

            $requestData['filter'] = TypeCaster::castArr($filter, [
                'offer_id'       => 'str',
                'posting_number' => 'str',
                'group_state'    => 'arrOfStr',
            ]);
        }

        $requestData = TypeCaster::castArr($requestData, ['limit' => 'int', 'last_id' => 'int']);

        return $this->request('POST', '/v2/returns/rfbs/list', $requestData);
    }

    /**
     * rFBS return info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ReturnsAPI_ReturnsRfbsGetV2
     *
     * @return array{returns?: array}
     */
    public function rfbsGet(int $returnId): array
    {
        return $this->request('POST', '/v2/returns/rfbs/get', ['return_id' => $returnId]);
    }
}
