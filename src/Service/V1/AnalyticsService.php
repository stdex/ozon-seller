<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * @psalm-type TAnalyticsFilter = array{
 *     key?: string,
 *     op?: 'EQ'|'GT'|'GTE'|'LT'|'LTE',
 *     value?: string
 * }
 * @psalm-type TAnalyticsSort = array{
 *     key?: string,
 *     order?: 'ASC'|'DESC'
 * }
 * @psalm-type TAnalyticsDataRow = array{
 *     dimensions?: list<array{id?: string, name?: string}>,
 *     metrics?: list<float>
 * }
 * @psalm-type TAnalyticsData = array{
 *     data?: list<TAnalyticsDataRow>,
 *     totals?: list<float>
 * }
 * @psalm-type TStockOnWarehouseRow = array{
 *     sku?: int,
 *     item_code?: string,
 *     item_name?: string,
 *     free_to_sell_amount?: int,
 *     promised_amount?: int,
 *     reserved_amount?: int,
 *     warehouse_name?: string
 * }
 * @psalm-type TTurnoverGrade = 'GRADES_NONE'|'GRADES_NOSALES'|'GRADES_GREEN'|'GRADES_YELLOW'|'GRADES_RED'|'GRADES_CRITICAL'
 * @psalm-type TTurnoverStocksRequest = array{
 *     sku?: list<string>,
 *     limit?: int,
 *     offset?: int
 * }
 * @psalm-type TTurnoverStocksResponse = array{
 *     items?: list<array{
 *         sku?: int,
 *         offer_id?: string,
 *         name?: string,
 *         ads?: float,
 *         current_stock?: int,
 *         idc?: float,
 *         idc_grade?: TTurnoverGrade,
 *         turnover?: float,
 *         turnover_grade?: TTurnoverGrade
 *     }>
 * }
 * @psalm-type TStocksRequest = array{
 *     skus: list<string>,
 *     cluster_ids?: list<string>,
 *     macrolocal_cluster_ids?: list<string>,
 *     warehouse_ids?: list<string>,
 *     item_tags?: list<'ITEM_ATTRIBUTE_NONE'|'ECONOM'|'NOVEL'|'DISCOUNT'|'FBS_RETURN'|'SUPER'|'MARKABLE'>,
 *     placement_zone?: list<string>,
 *     turnover_grades?: list<string>,
 *     unmarked_stocks_only?: bool
 * }
 * @psalm-type TStocksResponse = array{items?: list<array>}
 * @psalm-type TManageStocksRequest = array{
 *     filter?: array{
 *         skus?: list<string>,
 *         warehouse_ids?: list<string>,
 *         stock_types?: list<'STOCK_TYPE_VALID'|'STOCK_TYPE_WAITING_DOCS'|'STOCK_TYPE_EXPIRING'|'STOCK_TYPE_DEFECT'>
 *     },
 *     limit?: int,
 *     offset?: int
 * }
 * @psalm-type TManageStocksResponse = array{
 *     items?: list<array{
 *         sku?: int,
 *         offer_id?: string,
 *         name?: string,
 *         warehouse_name?: string,
 *         valid_stock_count?: int,
 *         waitingdocs_stock_count?: int,
 *         expiring_stock_count?: int,
 *         defect_stock_count?: int
 *     }>
 * }
 * @psalm-type TProductQueriesRequest = array{
 *     skus: list<string>,
 *     date_from: string,
 *     date_to?: string,
 *     page?: int,
 *     page_size?: int,
 *     sort_by?: 'BY_SEARCHES'|'BY_VIEWS'|'BY_POSITION'|'BY_CONVERSION'|'BY_GMV',
 *     sort_dir?: 'DESCENDING'|'ASCENDING'
 * }
 * @psalm-type TProductQueriesResponse = array{
 *     items?: list<array{
 *         sku?: int,
 *         offer_id?: string,
 *         name?: string,
 *         category?: string,
 *         currency?: string,
 *         gmv?: float,
 *         position?: float,
 *         unique_search_users?: int,
 *         unique_view_users?: int,
 *         view_conversion?: float
 *     }>,
 *     analytics_period?: array{date_from?: string, date_to?: string},
 *     page_count?: int,
 *     total?: int
 * }
 * @psalm-type TProductQueriesDetailsRequest = array{
 *     skus: list<string>,
 *     date_from: string,
 *     date_to?: string,
 *     limit_by_sku: int,
 *     page?: int,
 *     page_size?: int,
 *     sort_by?: 'BY_SEARCHES'|'BY_VIEWS'|'BY_POSITION'|'BY_CONVERSION'|'BY_GMV',
 *     sort_dir?: 'DESCENDING'|'ASCENDING'
 * }
 * @psalm-type TProductQueriesDetailsResponse = array{
 *     queries?: list<array{
 *         sku?: int,
 *         query?: string,
 *         query_index?: int,
 *         currency?: string,
 *         gmv?: float,
 *         order_count?: int,
 *         position?: float,
 *         unique_search_users?: int,
 *         unique_view_users?: int,
 *         view_conversion?: float
 *     }>,
 *     analytics_period?: array{date_from?: string, date_to?: string},
 *     page_count?: int,
 *     total?: int
 * }
 */
class AnalyticsService extends AbstractService
{
    private $path = '/v1/analytics';

    /**
     * Specify the period and metrics that are required.
     *
     * @see https://docs.ozon.ru/api/seller/en/#operation/AnalyticsAPI_AnalyticsGetData
     *
     * @param list<string>           $dimension
     * @param list<string>           $metrics
     * @param list<TAnalyticsFilter> $filters
     * @param list<TAnalyticsSort>   $sort
     *
     * @return TAnalyticsData
     */
    public function data(
        \DateTimeInterface $dateFrom,
        \DateTimeInterface $dateTo,
        array $dimension,
        array $metrics,
        int $offset = 0,
        int $limit = 10,
        array $filters = [],
        array $sort = []
    ): array {
        $body = [
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to'   => $dateTo->format('Y-m-d'),
            'dimension' => $dimension,
            'metrics'   => $metrics,
            'offset'    => $offset,
            'limit'     => $limit,
            'filters'   => $filters,
            'sort'      => $sort,
        ];

        return $this->request('POST', "{$this->path}/data", $body);
    }

    /**
     * Report on stocks and products movement at Ozon warehouses..
     *
     * @see https://docs.ozon.ru/api/seller/en/#operation/AnalyticsAPI_AnalyticsGetStockOnWarehouses
     *
     * @return array{rows?: list<TStockOnWarehouseRow>}
     */
    public function stockOnWarehouses(int $offset = 0, int $limit = 10): array
    {
        $body = [
            'offset' => $offset,
            'limit'  => $limit,
        ];

        return $this->request(
            'POST',
            "{$this->path}/stock_on_warehouses",
            $body
        );
    }

    /**
     * Method for getting a turnover report (FBO) by category for 15 days.
     *
     * @see https://docs.ozon.ru/api/seller/en/#operation/AnalyticsAPI_AnalyticsItemTurnoverDataV3
     */
    public function itemTurnover(\DateTimeInterface $dateFrom): array
    {
        $body = [
            'date_from' => $dateFrom->format('Y-m-d'),
        ];

        return $this->request('POST', "{$this->path}/item_turnover", $body);
    }

    /**
     * Отчёт по оборачиваемости товаров FBO.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/AnalyticsAPI_AnalyticsTurnoverStocks
     *
     * @param TTurnoverStocksRequest $requestData
     *
     * @return TTurnoverStocksResponse
     */
    public function turnoverStocks(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 10, 'offset' => 0],
            ArrayHelper::pick($requestData, ['sku', 'limit', 'offset'])
        );

        $requestData = TypeCaster::castArr($requestData, [
            'sku'    => 'arrOfStr',
            'limit'  => 'int',
            'offset' => 'int',
        ]);

        return $this->request('POST', "{$this->path}/turnover/stocks", $requestData);
    }

    /**
     * Аналитика по остаткам на складах Ozon.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/AnalyticsAPI_AnalyticsStocksV1
     *
     * @param TStocksRequest $requestData
     *
     * @return TStocksResponse
     */
    public function stocks(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'skus',
            'cluster_ids',
            'macrolocal_cluster_ids',
            'warehouse_ids',
            'item_tags',
            'placement_zone',
            'turnover_grades',
            'unmarked_stocks_only',
        ]);

        $requestData = TypeCaster::castArr($requestData, [
            'skus'                   => 'arrOfStr',
            'cluster_ids'            => 'arrOfStr',
            'macrolocal_cluster_ids' => 'arrOfStr',
            'warehouse_ids'          => 'arrOfStr',
            'item_tags'              => 'arrOfStr',
            'placement_zone'         => 'arrOfStr',
            'turnover_grades'        => 'arrOfStr',
            'unmarked_stocks_only'   => 'bool',
        ]);

        return $this->request('POST', "{$this->path}/stocks", $requestData);
    }

    /**
     * Управление остатками: количество товаров по типам остатков.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/AnalyticsAPI_AnalyticsManageStocks
     *
     * @param TManageStocksRequest $requestData
     *
     * @return TManageStocksResponse
     */
    public function manageStocks(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 100, 'offset' => 0],
            ArrayHelper::pick($requestData, ['filter', 'limit', 'offset'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['skus', 'warehouse_ids', 'stock_types']),
                ['skus' => 'arrOfStr', 'warehouse_ids' => 'arrOfStr', 'stock_types' => 'arrOfStr']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'limit'  => 'int',
            'offset' => 'int',
        ]);

        return $this->request('POST', "{$this->path}/manage/stocks", $requestData);
    }

    /**
     * Запросы товаров: поисковые запросы, по которым находят товары.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/AnalyticsAPI_ProductQueries
     *
     * @param TProductQueriesRequest $requestData
     *
     * @return TProductQueriesResponse
     */
    public function productQueries(array $requestData): array
    {
        $requestData = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($requestData, [
                'skus',
                'date_from',
                'date_to',
                'page',
                'page_size',
                'sort_by',
                'sort_dir',
            ])
        );

        $requestData = TypeCaster::castArr($requestData, [
            'skus'      => 'arrOfStr',
            'date_from' => 'str',
            'date_to'   => 'str',
            'page'      => 'int',
            'page_size' => 'int',
            'sort_by'   => 'str',
            'sort_dir'  => 'str',
        ]);

        return $this->request('POST', "{$this->path}/product-queries", $requestData);
    }

    /**
     * Детализация запросов товаров.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/AnalyticsAPI_ProductQueriesDetails
     *
     * @param TProductQueriesDetailsRequest $requestData
     *
     * @return TProductQueriesDetailsResponse
     */
    public function productQueriesDetails(array $requestData): array
    {
        $requestData = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($requestData, [
                'skus',
                'date_from',
                'date_to',
                'limit_by_sku',
                'page',
                'page_size',
                'sort_by',
                'sort_dir',
            ])
        );

        $requestData = TypeCaster::castArr($requestData, [
            'skus'         => 'arrOfStr',
            'date_from'    => 'str',
            'date_to'      => 'str',
            'limit_by_sku' => 'int',
            'page'         => 'int',
            'page_size'    => 'int',
            'sort_by'      => 'str',
            'sort_dir'     => 'str',
        ]);

        return $this->request('POST', "{$this->path}/product-queries/details", $requestData);
    }
}
