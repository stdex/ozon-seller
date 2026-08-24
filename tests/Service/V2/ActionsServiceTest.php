<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\ActionsService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\ActionsService
 */
final class ActionsServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ActionsService::class;
    }

    /**
     * @covers ::discountsTaskList
     */
    public function testDiscountsTaskList(): void
    {
        $this->quickTest(
            'discountsTaskList',
            ['APPROVED', 50, 10],
            [
                'POST',
                '/v2/actions/discounts-task/list',
                '{"status":"APPROVED","limit":50,"last_id":10}',
            ],
            '{"tasks":[]}',
            static function (array $result): void {
                self::assertSame(['tasks' => []], $result);
            }
        );
    }
}
