<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\FinanceService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\FinanceService
 */
final class FinanceServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return FinanceService::class;
    }

    /**
     * @covers ::realization
     */
    public function testRealization(): void
    {
        $this->quickTest(
            'realization',
            [2026, 8],
            [
                'POST',
                '/v2/finance/realization',
                '{"year":2026,"month":8}',
            ],
            '{"result":{"header":{"number":"1234"},"rows":[]}}'
        );
    }
}
