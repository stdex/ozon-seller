<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\InvoiceService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\InvoiceService
 */
final class InvoiceServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return InvoiceService::class;
    }

    /**
     * @covers ::fileUpload
     */
    public function testFileUpload(): void
    {
        $this->quickTest(
            'fileUpload',
            ['33920474-0032-1', 'JVBERi0='],
            [
                'POST',
                '/v1/invoice/file/upload',
                '{"posting_number":"33920474-0032-1","base64_content":"JVBERi0="}',
            ],
            '{"url":"https://cdn.ozone.ru/invoice.pdf"}',
            static function (array $result): void {
                self::assertSame(['url' => 'https://cdn.ozone.ru/invoice.pdf'], $result);
            }
        );
    }

    /**
     * @covers ::delete
     */
    public function testDelete(): void
    {
        $this->quickTest(
            'delete',
            ['33920474-0032-1'],
            ['POST', '/v1/invoice/delete', '{"posting_number":"33920474-0032-1"}'],
            '{"result":true}',
            static function ($result): void {
                self::assertTrue($result);
            }
        );
    }
}
