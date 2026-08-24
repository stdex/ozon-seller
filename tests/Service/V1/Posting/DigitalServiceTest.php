<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1\Posting;

use Gam6itko\OzonSeller\Service\V1\Posting\DigitalService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\Posting\DigitalService
 */
final class DigitalServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return DigitalService::class;
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $this->quickTest(
            'list',
            [
                [
                    'filter' => [
                        'since'          => '2026-08-01T00:00:00Z',
                        'to'             => '2026-08-08T00:00:00Z',
                        'posting_number' => ['33920474-0032-1'],
                    ],
                    'dir'    => 'DESC',
                    'limit'  => '50',
                    'offset' => '10',
                ],
            ],
            [
                'POST',
                '/v1/posting/digital/list',
                '{"limit":50,"offset":10,"dir":"DESC","filter":{"since":"2026-08-01T00:00:00Z","to":"2026-08-08T00:00:00Z","posting_number":["33920474-0032-1"]}}',
            ],
            '{"result":[]}'
        );
    }
}
