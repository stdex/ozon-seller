<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V3;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Типы описаны по var/swagger.json.
 *
 * @psalm-type TDateRange = array{from?: string, to?: string}
 * @psalm-type TTransactionFilter = array{
 *     date?: TDateRange,
 *     operation_type?: list<string>,
 *     posting_number?: string,
 *     transaction_type?: string
 * }
 * @psalm-type TTransactionListRequest = array{
 *     filter?: TTransactionFilter,
 *     page?: int,
 *     page_size?: int
 * }
 * @psalm-type TOperation = array{
 *     operation_id?: int,
 *     operation_type?: string,
 *     operation_type_name?: string,
 *     operation_date?: string,
 *     type?: string,
 *     amount?: float,
 *     accruals_for_sale?: float,
 *     sale_commission?: float,
 *     delivery_charge?: float,
 *     return_delivery_charge?: float,
 *     posting?: array,
 *     items?: list<array>,
 *     services?: list<array>
 * }
 * @psalm-type TTransactionListResult = array{
 *     operations?: list<TOperation>,
 *     page_count?: int,
 *     row_count?: int
 * }
 * @psalm-type TTransactionTotalsRequest = array{
 *     date?: TDateRange,
 *     posting_number?: string,
 *     transaction_type?: string
 * }
 * @psalm-type TTransactionTotalsResult = array{
 *     accruals_for_sale?: float,
 *     sale_commission?: float,
 *     processing_and_delivery?: float,
 *     refunds_and_cancellations?: float,
 *     services_amount?: float,
 *     compensation_amount?: float,
 *     money_transfer?: float,
 *     others_amount?: float
 * }
 */
class FinanceService extends AbstractService
{
    private $path = '/v3/finance';

    /**
     * Список транзакций.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FinanceAPI_FinanceTransactionListV3
     *
     * @param TTransactionListRequest $requestData
     *
     * @return TTransactionListResult
     */
    public function transactionList(array $requestData = []): array
    {
        $default = [
            'page'      => 1,
            'page_size' => 100,
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, ['filter', 'page', 'page_size'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = $this->pickFilter($requestData['filter']);
        }

        $requestData = TypeCaster::castArr($requestData, [
            'page'      => 'int',
            'page_size' => 'int',
        ]);

        return $this->request('POST', "{$this->path}/transaction/list", $requestData);
    }

    /**
     * Суммы транзакций.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FinanceAPI_FinanceTransactionTotalV3
     *
     * @param TTransactionTotalsRequest $requestData
     *
     * @return TTransactionTotalsResult
     */
    public function transactionTotals(array $requestData = []): array
    {
        $requestData = ArrayHelper::pick($requestData, ['date', 'posting_number', 'transaction_type']);

        if (isset($requestData['date'])) {
            $requestData['date'] = ArrayHelper::pick($requestData['date'], ['from', 'to']);
        }

        $requestData = TypeCaster::castArr($requestData, [
            'posting_number'   => 'str',
            'transaction_type' => 'str',
        ]);

        return $this->request('POST', "{$this->path}/transaction/totals", $requestData ?: '{}');
    }

    /**
     * @param array<array-key, mixed> $filter
     *
     * @return TTransactionFilter
     */
    private function pickFilter(array $filter): array
    {
        $filter = ArrayHelper::pick($filter, ['date', 'operation_type', 'posting_number', 'transaction_type']);

        if (isset($filter['date'])) {
            $filter['date'] = ArrayHelper::pick($filter['date'], ['from', 'to']);
        }

        return TypeCaster::castArr($filter, [
            'operation_type'   => 'arrOfStr',
            'posting_number'   => 'str',
            'transaction_type' => 'str',
        ]);
    }
}
