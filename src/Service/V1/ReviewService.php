<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Review comments. Available to sellers with a Premium Plus subscription.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TCommentCreateRequest = array{
 *     review_id: string,
 *     text: string,
 *     mark_review_as_processed?: bool,
 *     parent_comment_id?: string
 * }
 * @psalm-type TCommentCreateResponse = array{comment_id?: string}
 * @psalm-type TCommentListFilter = array{
 *     published_from?: string,
 *     published_to?: string,
 *     sku?: int
 * }
 * @psalm-type TCommentListRequest = array{
 *     review_id?: string,
 *     filter?: TCommentListFilter,
 *     limit?: int,
 *     offset?: int,
 *     sort_dir?: 'ASC'|'DESC'
 * }
 * @psalm-type TComment = array{
 *     id?: string,
 *     parent_comment_id?: string,
 *     text?: string,
 *     published_at?: string,
 *     is_official?: bool,
 *     is_owner?: bool,
 *     is_published?: bool,
 *     is_rejected?: bool,
 *     deviation_reason?: string,
 *     likes_amount?: int,
 *     dislikes_amount?: int
 * }
 * @psalm-type TCommentListResponse = array{
 *     comments?: list<TComment>,
 *     offset?: int
 * }
 */
class ReviewService extends AbstractService
{
    private $path = '/v1/review';

    /**
     * Leaves a comment on a review.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Review_CommentCreate
     *
     * @param TCommentCreateRequest $requestData
     *
     * @return TCommentCreateResponse
     */
    public function commentCreate(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'review_id',
            'text',
            'mark_review_as_processed',
            'parent_comment_id',
        ]);

        $requestData = TypeCaster::castArr($requestData, [
            'review_id'                => 'str',
            'text'                     => 'str',
            'mark_review_as_processed' => 'bool',
            'parent_comment_id'        => 'str',
        ]);

        return $this->request('POST', "{$this->path}/comment/create", $requestData);
    }

    /**
     * Retrieves a list of comments on a review.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Review_CommentList
     *
     * @param TCommentListRequest $requestData
     *
     * @return TCommentListResponse
     */
    public function commentList(array $requestData = []): array
    {
        $default = [
            'limit'    => 100,
            'offset'   => 0,
            'sort_dir' => 'ASC',
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, ['review_id', 'filter', 'limit', 'offset', 'sort_dir'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['published_from', 'published_to', 'sku']),
                ['published_from' => 'str', 'published_to' => 'str', 'sku' => 'int']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'review_id' => 'str',
            'limit'     => 'int',
            'offset'    => 'int',
            'sort_dir'  => 'str',
        ]);

        return $this->request('POST', "{$this->path}/comment/list", $requestData);
    }
}
