<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1\Posting;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * @psalm-type TCancelReasonData = array{
 *     id: int,
 *     title: string,
 *     type_id: string
 * }
 * @psalm-type TCancelReasonResponseData = array{
 *     posting_number: string,
 *     reasons: TCancelReasonData[]
 * }
 */
class FbsService extends AbstractService
{
    private $path = '/v1/posting/fbs';

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetLabelBatch
     */
    public function packageLabelGet(int $taskId): array
    {
        $body = [
            'task_id' => $taskId,
        ];

        return $this->request('POST', "{$this->path}/package-label/get", $body);
    }

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetPostingFbsCancelReasonV1
     *
     * @param string[] $postingNumbers shipment numbers
     *
     * @return TCancelReasonResponseData[]
     */
    public function cancelReason(array $postingNumbers): array
    {
        if (empty($postingNumbers)) {
            throw new \InvalidArgumentException('Empty posting list');
        }

        $body = [
            'related_posting_numbers' => $postingNumbers,
        ];

        return $this->request('POST', "{$this->path}/cancel-reason", $body);
    }

    /**
     * Splits a posting into several ones.
     *
     * Nested `products` of every posting is passed as is.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingFbsSplit
     *
     * @param list<array{products: list<array>}> $postings
     *
     * @return array{parent_posting?: array, postings?: list<array>}
     */
    public function split(string $postingNumber, array $postings): array
    {
        $postings = array_map(static function (array $posting): array {
            return ArrayHelper::pick($posting, ['products']);
        }, $postings);

        return $this->request('POST', "{$this->path}/split", [
            'posting_number' => $postingNumber,
            'postings'       => $postings,
        ]);
    }

    /**
     * Splits a posting by traceable products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingFbsTraceableSplit
     *
     * @return array{postings?: list<array>}
     */
    public function traceableSplit(string $postingNumber): array
    {
        return $this->request('POST', "{$this->path}/traceable/split", [
            'posting_number' => $postingNumber,
        ]);
    }

    /**
     * Traceability attributes of the posting products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingFbsProductTraceableAttribute
     *
     * @return array{products?: list<array>}
     */
    public function productTraceableAttribute(string $postingNumber): array
    {
        return $this->request('POST', "{$this->path}/product/traceable/attribute", [
            'posting_number' => $postingNumber,
        ]);
    }

    /**
     * How many times the delivery timeslot can still be changed.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingFbsTimeslotChangeRestrictions
     *
     * @return array{delivery_interval?: array{begin?: string, end?: string}, remaining_changes_count?: int}
     */
    public function timeslotChangeRestrictions(string $postingNumber): array
    {
        return $this->request('POST', "{$this->path}/timeslot/change-restrictions", [
            'posting_number' => $postingNumber,
        ]);
    }

    /**
     * Changes the delivery timeslot of a posting.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingFbsTimeslotSet
     */
    public function timeslotSet(string $postingNumber, string $from, string $to): bool
    {
        return true === $this->request('POST', "{$this->path}/timeslot/set", [
            'posting_number' => $postingNumber,
            'new_timeslot'   => [
                'from' => $from,
                'to'   => $to,
            ],
        ]);
    }

    /**
     * Weight, size and price restrictions of a posting.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingFbsRestrictions
     *
     * @return array{
     *     posting_number?: string,
     *     max_posting_weight?: float,
     *     min_posting_weight?: float,
     *     width?: float,
     *     length?: float,
     *     height?: float,
     *     max_posting_price?: float,
     *     min_posting_price?: float
     * }
     */
    public function restrictions(string $postingNumber): array
    {
        return $this->request('POST', "{$this->path}/restrictions", ['posting_number' => $postingNumber]);
    }

    /**
     * Requests generation of package labels. The result is fetched by self::packageLabelGet.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingFbsPackageLabelCreate
     *
     * @param list<string> $postingNumbers
     *
     * @return array{task_id?: int}
     */
    public function packageLabelCreate(array $postingNumbers): array
    {
        return $this->request('POST', "{$this->path}/package-label/create", [
            'posting_number' => array_map('strval', $postingNumbers),
        ]);
    }

    /**
     * Checks the pick-up code of a posting.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingFbsPickUpCodeVerify
     *
     * @return array{valid?: bool}
     */
    public function pickUpCodeVerify(string $postingNumber, string $pickupCode): array
    {
        return $this->request('POST', "{$this->path}/pick-up-code/verify", [
            'posting_number' => $postingNumber,
            'pickup_code'    => $pickupCode,
        ]);
    }

    /**
     * Cancels an rFBS posting.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingCancel
     *
     * @return array{message?: string}
     */
    public function cancel(string $postingNumber, int $reasonId, string $reasonMessage = ''): array
    {
        $requestData = [
            'posting_number' => $postingNumber,
            'reason_id'      => $reasonId,
        ];

        if ('' !== $reasonMessage) {
            $requestData['reason_message'] = $reasonMessage;
        }

        return $this->request('POST', '/v1/posting/cancel', $requestData);
    }

    /**
     * Status of an rFBS posting cancellation.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingCancelStatus
     *
     * @return array{state?: string, order_number?: string, posting_number?: list<string>}
     */
    public function cancelStatus(string $postingNumber): array
    {
        return $this->request('POST', '/v1/posting/cancel/status', ['posting_number' => $postingNumber]);
    }

    /**
     * Marking codes of the postings exemplars.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingMarks
     *
     * @param list<string> $postingNumbers
     *
     * @return array{issued_exemplars?: list<array>, non_issued_exemplars?: list<array>, invalid_postings?: list<string>}
     */
    public function marks(array $postingNumbers): array
    {
        return $this->request('POST', '/v1/posting/marks', [
            'posting_numbers' => array_map('strval', $postingNumbers),
        ]);
    }

    /**
     * Moves the shipment date of a posting.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingCutoffSet
     */
    public function cutoffSet(string $postingNumber, string $newCutoffDate): bool
    {
        return true === $this->request('POST', '/v1/posting/cutoff/set', [
            'posting_number'  => $postingNumber,
            'new_cutoff_date' => $newCutoffDate,
        ]);
    }

    /**
     * Products of unpaid legal entity postings.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingUnpaidLegalProductList
     *
     * @return array{products?: list<array>, cursor?: string}
     */
    public function unpaidLegalProductList(int $limit = 100, string $cursor = ''): array
    {
        $requestData = ['limit' => $limit];

        if ('' !== $cursor) {
            $requestData['cursor'] = $cursor;
        }

        return $this->request('POST', '/v1/posting/unpaid-legal/product/list', $requestData);
    }

    /**
     * Confirms that the exemplars data of a posting is complete.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_FbsPostingProductExemplarUpdate
     */
    public function productExemplarUpdate(string $postingNumber): array
    {
        return $this->request('POST', '/v1/fbs/posting/product/exemplar/update', [
            'posting_number' => $postingNumber,
        ]);
    }

    /**
     * Uploads digital product codes of a posting.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingDigitalCodesUpload
     *
     * @param list<array{sku: int, exemplar_qty: int, not_available_exemplar_qty: int, exemplar_keys?: list<string>}> $exemplarsBySku
     *
     * @return array{exemplars_by_sku?: list<array>}
     */
    public function digitalCodesUpload(string $postingNumber, array $exemplarsBySku): array
    {
        $exemplarsBySku = array_map(static function (array $item): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($item, ['sku', 'exemplar_qty', 'not_available_exemplar_qty', 'exemplar_keys']),
                [
                    'sku'                        => 'int',
                    'exemplar_qty'               => 'int',
                    'not_available_exemplar_qty' => 'int',
                    'exemplar_keys'              => 'arrOfStr',
                ]
            );
        }, $exemplarsBySku);

        return $this->request('POST', '/v1/posting/digital/codes/upload', [
            'posting_number'   => $postingNumber,
            'exemplars_by_sku' => $exemplarsBySku,
        ]);
    }

    /**
     * ETGB customs declarations of the global postings.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingGlobalEtgb
     *
     * @return list<array{posting_number?: string, etgb?: array}>
     */
    public function globalEtgb(string $dateFrom, string $dateTo): array
    {
        return $this->request('POST', '/v1/posting/global/etgb', [
            'date' => [
                'from' => $dateFrom,
                'to'   => $dateTo,
            ],
        ]);
    }
}
