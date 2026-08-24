<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Types are derived from var/swagger.json.
 *
 * @psalm-type TRealizationResult = array{
 *     header?: array{
 *         number?: string,
 *         doc_date?: string,
 *         contract_date?: string,
 *         contract_number?: string,
 *         currency_sys_name?: string,
 *         payer_inn?: string,
 *         payer_kpp?: string,
 *         payer_name?: string,
 *         receiver_inn?: string,
 *         receiver_kpp?: string,
 *         receiver_name?: string,
 *         start_date?: string,
 *         stop_date?: string
 *     },
 *     rows?: list<array{
 *         rowNumber?: int,
 *         item?: array,
 *         commission_ratio?: float,
 *         delivery_commission?: array,
 *         return_commission?: array,
 *         seller_price_per_instance?: float
 *     }>
 * }
 */
class FinanceService extends AbstractService
{
    private $path = '/v2/finance';

    /**
     * Products realization report (version 2).
     *
     * @see https://docs.ozon.ru/api/seller/#operation/FinanceAPI_CreateReportF1createV2
     *
     * @return TRealizationResult
     */
    public function realization(int $year, int $month): array
    {
        return $this->request('POST', "{$this->path}/realization", [
            'year'  => $year,
            'month' => $month,
        ]);
    }
}
