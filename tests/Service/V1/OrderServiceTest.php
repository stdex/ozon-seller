<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\OrderService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\OrderService
 */
final class OrderServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return OrderService::class;
    }

    /**
     * @covers ::cancel
     */
    public function testCancel(): void
    {
        $this->quickTest(
            'cancel',
            ['33920474-0032', 352, 'нет товара'],
            [
                'POST',
                '/v1/order/cancel',
                '{"order_number":"33920474-0032","reason_id":352,"reason_message":"нет товара"}',
            ],
            '{"message":"ok"}',
            static function (array $result): void {
                self::assertSame(['message' => 'ok'], $result);
            }
        );
    }

    /**
     * @covers ::cancelCheck
     */
    public function testCancelCheck(): void
    {
        $this->quickTest(
            'cancelCheck',
            ['33920474-0032'],
            ['POST', '/v1/order/cancel/check', '{"order_number":"33920474-0032"}'],
            '{"cancellable":true,"order_number":"33920474-0032"}',
            static function (array $result): void {
                self::assertTrue($result['cancellable']);
            }
        );
    }

    /**
     * @covers ::cancelStatus
     */
    public function testCancelStatus(): void
    {
        $this->quickTest(
            'cancelStatus',
            ['33920474-0032'],
            ['POST', '/v1/order/cancel/status', '{"order_number":"33920474-0032"}'],
            '{"state":"SUCCESS","posting_number":["33920474-0032-1"]}',
            static function (array $result): void {
                self::assertSame('SUCCESS', $result['state']);
            }
        );
    }
}
