<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Invoices for cross-border postings.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class InvoiceService extends AbstractService
{
    /**
     * Uploads an invoice as a base64-encoded PDF and returns its url.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/InvoiceAPI_InvoiceFileUpload
     *
     * @return array{url?: string}
     */
    public function fileUpload(string $postingNumber, string $base64Content): array
    {
        return $this->request('POST', '/v1/invoice/file/upload', [
            'posting_number' => $postingNumber,
            'base64_content' => $base64Content,
        ]);
    }

    /**
     * Deletes the invoice of a posting.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/InvoiceAPI_InvoiceDelete
     */
    public function delete(string $postingNumber): bool
    {
        return true === $this->request('POST', '/v1/invoice/delete', ['posting_number' => $postingNumber]);
    }
}
