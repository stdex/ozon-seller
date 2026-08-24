<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\ReportService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\ReportService
 */
final class ReportServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ReportService::class;
    }

    /**
     * @covers ::returnsCreate
     */
    public function testReturnsCreate(): void
    {
        $this->quickTest(
            'returnsCreate',
            [
                [
                    'filter' => [
                        'date_from'       => '2026-08-01T00:00:00.000Z',
                        'date_to'         => '2026-08-08T00:00:00.000Z',
                        'status'          => 'Approved',
                        'delivery_schema' => 'FBS',
                        // must be filtered out
                        'foo'             => 'bar',
                    ],
                    'language' => 'RU',
                ],
            ],
            [
                'POST',
                '/v2/report/returns/create',
                '{"language":"RU","filter":{"date_from":"2026-08-01T00:00:00.000Z","date_to":"2026-08-08T00:00:00.000Z","status":"Approved","delivery_schema":"FBS"}}',
            ],
            '{"result":{"code":"report-code"}}'
        );
    }
}
