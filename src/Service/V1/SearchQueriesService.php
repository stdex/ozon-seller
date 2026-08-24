<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Customer search queries. Available to sellers with a Premium Plus subscription.
 *
 * In the specification limit, offset and total are strings (int64).
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TSearchQuery = array{
 *     query?: string,
 *     add_to_cart?: float,
 *     avg_price?: float,
 *     client_count?: float,
 *     conversion_to_cart?: float,
 *     items_views?: float,
 *     sellers_count?: float
 * }
 * @psalm-type TSearchQueriesResponse = array{
 *     search_queries?: list<TSearchQuery>,
 *     offset?: string,
 *     total?: string
 * }
 */
class SearchQueriesService extends AbstractService
{
    private $path = '/v1/search-queries';

    /**
     * Search queries similar to the given text.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SearchQueriesAPI_SearchQueriesText
     *
     * @param string $sortBy  CLIENT_COUNT|ADD_TO_CART|CONVERSION_TO_CART|AVG_PRICE
     * @param string $sortDir ASC|DESC
     *
     * @return TSearchQueriesResponse
     */
    public function text(string $text, int $limit = 10, int $offset = 0, string $sortBy = 'CLIENT_COUNT', string $sortDir = 'DESC'): array
    {
        return $this->request('POST', "{$this->path}/text", [
            'text'     => $text,
            'limit'    => (string) $limit,
            'offset'   => (string) $offset,
            'sort_by'  => $sortBy,
            'sort_dir' => $sortDir,
        ]);
    }

    /**
     * Top search queries.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SearchQueriesAPI_SearchQueriesTop
     *
     * @return TSearchQueriesResponse
     */
    public function top(int $limit = 10, int $offset = 0): array
    {
        return $this->request('POST', "{$this->path}/top", [
            'limit'  => (string) $limit,
            'offset' => (string) $offset,
        ]);
    }
}
