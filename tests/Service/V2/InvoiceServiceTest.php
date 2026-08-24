<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\InvoiceService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\InvoiceService
 */
final class InvoiceServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return InvoiceService::class;
    }

    /**
     * @covers ::createOrUpdate
     */
    public function testCreateOrUpdate(): void
    {
        $this->quickTest(
            'createOrUpdate',
            [
                [
                    'posting_number' => '33920474-0032-1',
                    'url'            => 'https://cdn.ozone.ru/invoice.pdf',
                    'date'           => '2026-08-25T00:00:00Z',
                    'number'         => 'INV-1',
                    'price'          => '1000.5',
                    'price_currency' => 'USD',
                    'hs_codes'       => [['code' => '1234567890']],
                    // must be filtered out
                    'foo'            => 'bar',
                ],
            ],
            [
                'POST',
                '/v2/invoice/create-or-update',
                '{"posting_number":"33920474-0032-1","url":"https:\/\/cdn.ozone.ru\/invoice.pdf","date":"2026-08-25T00:00:00Z","number":"INV-1","price":1000.5,"price_currency":"USD","hs_codes":[{"code":"1234567890"}]}',
            ],
            '{"result":true}',
            static function ($result): void {
                self::assertTrue($result);
            }
        );
    }

    /**
     * @covers ::get
     */
    public function testGet(): void
    {
        $this->quickTest(
            'get',
            ['33920474-0032-1'],
            ['POST', '/v2/invoice/get', '{"posting_number":"33920474-0032-1"}'],
            '{"result":{"number":"INV-1","file_url":"https://cdn.ozone.ru/invoice.pdf"}}'
        );
    }
}
