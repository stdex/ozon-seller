<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Reports on products removed from supplies and stocks.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TListResponse = array{
 *     returns_summary_report_rows?: list<array>,
 *     last_id?: string
 * }
 */
class RemovalService extends AbstractService
{
    private $path = '/v1/removal';

    /**
     * Products removed from supplies.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/RemovalAPI_RemovalFromSupplyList
     *
     * @return TListResponse
     */
    public function fromSupplyList(string $dateFrom, string $dateTo, int $limit = 100, string $lastId = ''): array
    {
        return $this->request('POST', "{$this->path}/from-supply/list", $this->pagination($dateFrom, $dateTo, $limit, $lastId));
    }

    /**
     * Products removed from stocks.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/RemovalAPI_RemovalFromStockList
     *
     * @return TListResponse
     */
    public function fromStockList(string $dateFrom, string $dateTo, int $limit = 100, string $lastId = ''): array
    {
        return $this->request('POST', "{$this->path}/from-stock/list", $this->pagination($dateFrom, $dateTo, $limit, $lastId));
    }

    /**
     * @return array{date_from: string, date_to: string, limit: int, last_id?: string}
     */
    private function pagination(string $dateFrom, string $dateTo, int $limit, string $lastId): array
    {
        $requestData = [
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'limit'     => $limit,
        ];

        if ('' !== $lastId) {
            $requestData['last_id'] = $lastId;
        }

        return $requestData;
    }
}
