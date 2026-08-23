<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Рейтинги продавца.
 *
 * Типы описаны по var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TRatingItem = array{
 *     name?: string,
 *     rating?: string,
 *     current_value?: float,
 *     past_value?: float,
 *     value_type?: string,
 *     status?: string,
 *     rating_direction?: string,
 *     change?: array
 * }
 * @psalm-type TSummaryResponse = array{
 *     groups?: list<array{group_name?: string, items?: list<TRatingItem>}>,
 *     localization_index?: list<array{calculation_date?: string, localization_percentage?: int}>,
 *     penalty_score_exceeded?: bool,
 *     premium?: bool,
 *     premium_plus?: bool
 * }
 * @psalm-type THistoryRequest = array{
 *     date_from: string,
 *     date_to: string,
 *     ratings: list<string>,
 *     with_premium_scores?: bool
 * }
 * @psalm-type THistoryResponse = array{
 *     ratings?: list<array{
 *         rating?: string,
 *         danger_threshold?: float,
 *         premium_threshold?: float,
 *         warning_threshold?: float,
 *         values?: list<array>
 *     }>,
 *     premium_scores?: list<array{rating?: string, scores?: list<array>}>
 * }
 * @psalm-type TIndexFbsInfoResponse = array{
 *     index?: float,
 *     currency_code?: string,
 *     period_from?: string,
 *     period_to?: string,
 *     processing_costs_sum?: float,
 *     defects?: list<array{date?: string, index_by_date?: float, processing_costs_sum_by_date?: float}>
 * }
 * @psalm-type TIndexFbsPostingListRequest = array{
 *     filter: array{date_from: string, date_to: string, posting_numbers?: list<string>},
 *     cursor?: string,
 *     limit?: int
 * }
 * @psalm-type TIndexFbsPostingListResponse = array{
 *     errors?: list<array{
 *         posting_number?: string,
 *         posting_error_type?: 'UNSPECIFIED'|'SELLER_CANCELLATION'|'SELLER_DELAY',
 *         error_at?: string,
 *         index?: float,
 *         has_grace_status?: bool,
 *         delivery_schema?: string,
 *         charge_percent?: float,
 *         charge_price?: float,
 *         charge_price_currency_code?: string,
 *         product_price?: float,
 *         product_price_currency_code?: string
 *     }>,
 *     cursor?: string,
 *     has_next?: bool
 * }
 */
class RatingService extends AbstractService
{
    private $path = '/v1/rating';

    /**
     * Информация о рейтингах продавца.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/RatingAPI_RatingSummaryV1
     *
     * @return TSummaryResponse
     */
    public function summary(): array
    {
        return $this->request('POST', "{$this->path}/summary", '{}');
    }

    /**
     * Информация о рейтингах продавца за период.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/RatingAPI_RatingHistoryV1
     *
     * @param THistoryRequest $requestData
     *
     * @return THistoryResponse
     */
    public function history(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'date_from',
            'date_to',
            'ratings',
            'with_premium_scores',
        ]);

        $requestData = TypeCaster::castArr($requestData, [
            'date_from'           => 'str',
            'date_to'             => 'str',
            'ratings'             => 'arrOfStr',
            'with_premium_scores' => 'bool',
        ]);

        return $this->request('POST', "{$this->path}/history", $requestData);
    }

    /**
     * Информация об индексе локализации FBS.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/RatingAPI_IndexFbsInfo
     *
     * @return TIndexFbsInfoResponse
     */
    public function indexFbsInfo(): array
    {
        return $this->request('POST', "{$this->path}/index/fbs/info", '{}');
    }

    /**
     * Отправления, которые повлияли на индекс FBS.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/RatingAPI_IndexFbsPostingList
     *
     * @param TIndexFbsPostingListRequest $requestData
     *
     * @return TIndexFbsPostingListResponse
     */
    public function indexFbsPostingList(array $requestData): array
    {
        $requestData = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($requestData, ['filter', 'cursor', 'limit'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['date_from', 'date_to', 'posting_numbers']),
                ['date_from' => 'str', 'date_to' => 'str', 'posting_numbers' => 'arrOfStr']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'cursor' => 'str',
            'limit'  => 'int',
        ]);

        return $this->request('POST', "{$this->path}/index/fbs/posting/list", $requestData);
    }
}
