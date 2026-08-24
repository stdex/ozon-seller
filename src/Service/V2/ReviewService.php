<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Reviews. Available to sellers with a Premium Plus subscription.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TReviewStatus = 'ALL'|'NEW'|'VIEWED'|'PROCESSED'
 * @psalm-type TListFilters = array{
 *     order_status?: 'ALL'|'DELIVERED'|'CANCELLED',
 *     published_from?: string,
 *     published_to?: string,
 *     skus?: list<string>,
 *     status?: TReviewStatus
 * }
 * @psalm-type TListRequest = array{
 *     filters?: TListFilters,
 *     last_id?: string,
 *     limit?: int,
 *     sort_dir?: 'ASC'|'DESC'
 * }
 * @psalm-type TReview = array{
 *     id?: string,
 *     sku?: int,
 *     text?: string,
 *     rating?: int,
 *     status?: 'NEW'|'VIEWED'|'PROCESSED',
 *     order_status?: 'DELIVERED'|'CANCELLED',
 *     published_at?: string,
 *     comments_amount?: int,
 *     photos_amount?: int,
 *     videos_amount?: int,
 *     is_rating_participant?: bool
 * }
 * @psalm-type TListResponse = array{
 *     reviews?: list<TReview>,
 *     last_id?: string,
 *     has_next?: bool
 * }
 * @psalm-type TInfoResponse = array{
 *     id?: string,
 *     sku?: int,
 *     text?: string,
 *     rating?: int,
 *     status?: 'NEW'|'VIEWED'|'PROCESSED',
 *     order_status?: 'DELIVERED'|'CANCELLED',
 *     published_at?: string,
 *     comments_amount?: int,
 *     photos_amount?: int,
 *     videos_amount?: int,
 *     likes_amount?: int,
 *     dislikes_amount?: int,
 *     is_rating_participant?: bool,
 *     photos?: list<array{url?: string, width?: int, height?: int}>,
 *     videos?: list<array{url?: string, width?: int, height?: int, preview_url?: string, short_video_preview_url?: string}>
 * }
 * @psalm-type TCountResponse = array{
 *     total?: int,
 *     new?: int,
 *     viewed?: int,
 *     processed?: int
 * }
 */
class ReviewService extends AbstractService
{
    private $path = '/v2/review';

    /**
     * Retrieves a list of reviews.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Review_ListV2
     *
     * @param TListRequest $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData = []): array
    {
        $default = [
            'limit'    => 100,
            'sort_dir' => 'ASC',
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, ['filters', 'last_id', 'limit', 'sort_dir'])
        );

        if (isset($requestData['filters'])) {
            $requestData['filters'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filters'], [
                    'order_status',
                    'published_from',
                    'published_to',
                    'skus',
                    'status',
                ]),
                [
                    'order_status'   => 'str',
                    'published_from' => 'str',
                    'published_to'   => 'str',
                    'skus'           => 'arrOfStr',
                    'status'         => 'str',
                ]
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'last_id'  => 'str',
            'limit'    => 'int',
            'sort_dir' => 'str',
        ]);

        return $this->request('POST', "{$this->path}/list", $requestData);
    }

    /**
     * Retrieves review info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Review_InfoV2
     *
     * @return TInfoResponse
     */
    public function info(string $reviewId): array
    {
        return $this->request('POST', "{$this->path}/info", ['review_id' => $reviewId]);
    }

    /**
     * Retrieves the number of reviews by status.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Review_CountV2
     *
     * @return TCountResponse
     */
    public function count(): array
    {
        return $this->request('POST', "{$this->path}/count", '{}');
    }

    /**
     * Changes the status of reviews.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Review_ChangeStatusV2
     *
     * @param list<string> $reviewIds
     * @param TReviewStatus $status
     */
    public function changeStatus(array $reviewIds, string $status): array
    {
        return $this->request('POST', "{$this->path}/change-status", [
            'review_ids' => array_map('strval', $reviewIds),
            'status'     => $status,
        ]);
    }

    /**
     * Deletes a comment on a review.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Review_CommentDeleteV2
     */
    public function commentDelete(string $commentId, int $sku): array
    {
        return $this->request('POST', "{$this->path}/comment/delete", [
            'comment_id' => $commentId,
            'sku'        => $sku,
        ]);
    }
}
