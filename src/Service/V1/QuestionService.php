<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Product questions. Available to sellers with a Premium Plus subscription.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TQuestionStatus = 'ALL'|'NEW'|'VIEWED'|'PROCESSED'|'UNPROCESSED'
 * @psalm-type TListFilter = array{
 *     date_from?: string,
 *     date_to?: string,
 *     status?: TQuestionStatus
 * }
 * @psalm-type TListRequest = array{
 *     filter?: TListFilter,
 *     last_id?: string,
 *     limit?: int,
 *     sort_dir?: 'ASC'|'DESC'
 * }
 * @psalm-type TQuestion = array{
 *     id?: string,
 *     sku?: int,
 *     text?: string,
 *     author_name?: string,
 *     answers_count?: int,
 *     product_url?: string,
 *     question_link?: string,
 *     published_at?: string,
 *     status?: TQuestionStatus
 * }
 * @psalm-type TListResponse = array{
 *     questions?: list<TQuestion>,
 *     last_id?: string,
 *     has_next?: bool
 * }
 * @psalm-type TCountResponse = array{
 *     all?: int,
 *     new?: int,
 *     viewed?: int,
 *     processed?: int,
 *     unprocessed?: int
 * }
 * @psalm-type TAnswer = array{
 *     id?: string,
 *     question_id?: string,
 *     sku?: int,
 *     text?: string,
 *     author_name?: string,
 *     published_at?: string,
 *     status_publication?: string
 * }
 * @psalm-type TAnswerListResponse = array{
 *     answers?: list<TAnswer>,
 *     last_id?: string
 * }
 */
class QuestionService extends AbstractService
{
    private $path = '/v1/question';

    /**
     * Questions list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Question_List
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
            ArrayHelper::pick($requestData, ['filter', 'last_id', 'limit', 'sort_dir'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['date_from', 'date_to', 'status']),
                ['date_from' => 'str', 'date_to' => 'str', 'status' => 'str']
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
     * Question info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Question_Info
     *
     * @return TQuestion
     */
    public function info(string $questionId): array
    {
        return $this->request('POST', "{$this->path}/info", ['question_id' => $questionId]);
    }

    /**
     * Number of questions by status.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Question_Count
     *
     * @return TCountResponse
     */
    public function count(): array
    {
        return $this->request('POST', "{$this->path}/count", '{}');
    }

    /**
     * Changes the status of questions.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Question_ChangeStatus
     *
     * @param list<string>    $questionIds
     * @param TQuestionStatus $status
     */
    public function changeStatus(array $questionIds, string $status): array
    {
        return $this->request('POST', "{$this->path}/change-status", [
            'question_ids' => array_map('strval', $questionIds),
            'status'       => $status,
        ]);
    }

    /**
     * Products with the largest number of questions.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Question_TopSku
     *
     * @return array{sku?: list<string>}
     */
    public function topSku(int $limit = 10): array
    {
        return $this->request('POST', "{$this->path}/top-sku", ['limit' => $limit]);
    }

    /**
     * Creates an answer to a question.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Question_AnswerCreate
     *
     * @return array{answer_id?: string}
     */
    public function answerCreate(string $questionId, int $sku, string $text): array
    {
        return $this->request('POST', "{$this->path}/answer/create", [
            'question_id' => $questionId,
            'sku'         => $sku,
            'text'        => $text,
        ]);
    }

    /**
     * Deletes an answer to a question.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Question_AnswerDelete
     */
    public function answerDelete(string $answerId, int $sku): array
    {
        return $this->request('POST', "{$this->path}/answer/delete", [
            'answer_id' => $answerId,
            'sku'       => $sku,
        ]);
    }

    /**
     * Answers to a question.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/Question_AnswerList
     *
     * @return TAnswerListResponse
     */
    public function answerList(string $questionId, int $sku, string $lastId = ''): array
    {
        $requestData = [
            'question_id' => $questionId,
            'sku'         => $sku,
        ];

        if ('' !== $lastId) {
            $requestData['last_id'] = $lastId;
        }

        return $this->request('POST', "{$this->path}/answer/list", $requestData);
    }
}
