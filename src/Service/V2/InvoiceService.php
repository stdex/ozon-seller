<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Invoices for cross-border postings.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TInvoiceRequest = array{
 *     posting_number: string,
 *     url: string,
 *     date: string,
 *     number?: string,
 *     price?: float,
 *     price_currency?: string,
 *     hs_codes?: list<array>
 * }
 * @psalm-type TInvoiceResult = array{
 *     date?: string,
 *     file_url?: string,
 *     number?: string,
 *     price?: float,
 *     price_currency?: string,
 *     hs_codes?: list<array>
 * }
 */
class InvoiceService extends AbstractService
{
    private $path = '/v2/invoice';

    /**
     * Creates or updates the invoice of a posting.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/InvoiceAPI_InvoiceCreateOrUpdateV2
     *
     * @param TInvoiceRequest $requestData
     */
    public function createOrUpdate(array $requestData): bool
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'posting_number',
                'url',
                'date',
                'number',
                'price',
                'price_currency',
                'hs_codes',
            ]),
            [
                'posting_number' => 'str',
                'url'            => 'str',
                'date'           => 'str',
                'number'         => 'str',
                'price'          => 'float',
                'price_currency' => 'str',
            ]
        );

        return true === $this->request('POST', "{$this->path}/create-or-update", $requestData);
    }

    /**
     * Invoice of a posting.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/InvoiceAPI_InvoiceGetV2
     *
     * @return TInvoiceResult
     */
    public function get(string $postingNumber): array
    {
        return $this->request('POST', "{$this->path}/get", ['posting_number' => $postingNumber]);
    }
}
