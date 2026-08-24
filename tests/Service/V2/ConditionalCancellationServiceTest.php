<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\ConditionalCancellationService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\ConditionalCancellationService
 */
final class ConditionalCancellationServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ConditionalCancellationService::class;
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
                    'filters' => [
                        'cancellation_initiator' => ['CLIENT'],
                        'posting_number'         => ['33920474-0032-1'],
                        'state'                  => 'ON_APPROVAL',
                        // must be filtered out
                        'foo'                    => 'bar',
                    ],
                    'with'    => ['counter' => true, 'foo' => 'bar'],
                    'limit'   => '50',
                    'last_id' => '10',
                ],
            ],
            [
                'POST',
                '/v2/conditional-cancellation/list',
                '{"limit":50,"filters":{"cancellation_initiator":["CLIENT"],"posting_number":["33920474-0032-1"],"state":"ON_APPROVAL"},"with":{"counter":true},"last_id":10}',
            ],
            '{"result":[],"counter":0,"last_id":0}',
            static function (array $result): void {
                self::assertSame(['result' => [], 'counter' => 0, 'last_id' => 0], $result);
            }
        );
    }

    /**
     * @covers ::approve
     */
    public function testApprove(): void
    {
        $this->quickTest(
            'approve',
            [123, 'ок'],
            [
                'POST',
                '/v2/conditional-cancellation/approve',
                '{"cancellation_id":123,"comment":"ок"}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::reject
     */
    public function testReject(): void
    {
        $this->quickTest(
            'reject',
            [123],
            [
                'POST',
                '/v2/conditional-cancellation/reject',
                '{"cancellation_id":123}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }
}
