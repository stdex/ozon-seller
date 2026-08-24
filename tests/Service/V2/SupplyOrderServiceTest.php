<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\SupplyOrderService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\SupplyOrderService
 */
final class SupplyOrderServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return SupplyOrderService::class;
    }

    /**
     * @covers ::timeslotList
     */
    public function testTimeslotList(): void
    {
        $this->quickTest(
            'timeslotList',
            [123],
            ['POST', '/v2/supply-order/timeslot/list', '{"order_id":123}'],
            '{"timeslots_info":{"timeslots":[]}}',
            static function (array $result): void {
                self::assertSame(['timeslots_info' => ['timeslots' => []]], $result);
            }
        );
    }
}
