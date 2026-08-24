<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TReturnsFilter = array{
 *     date_from: string,
 *     date_to: string,
 *     status: string,
 *     delivery_schema?: 'FBS'|'FBO'|'ALL'
 * }
 * @psalm-type TReturnsRequest = array{
 *     filter: TReturnsFilter,
 *     language?: string
 * }
 */
class ReportService extends AbstractService
{
    /**
     * Returns report. Returns a report code for \Gam6itko\OzonSeller\Service\V1\ReportService::info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ReportAPI_CreateReturnsReportV2
     *
     * @param TReturnsRequest $requestData
     *
     * @return array{code?: string}
     */
    public function returnsCreate(array $requestData): array
    {
        $requestData = array_merge(
            ['language' => 'DEFAULT'],
            ArrayHelper::pick($requestData, ['filter', 'language'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], [
                    'date_from',
                    'date_to',
                    'status',
                    'delivery_schema',
                ]),
                [
                    'date_from'       => 'str',
                    'date_to'         => 'str',
                    'status'          => 'str',
                    'delivery_schema' => 'str',
                ]
            );
        }

        return $this->request('POST', '/v2/report/returns/create', $requestData);
    }
}
