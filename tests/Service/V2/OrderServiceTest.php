<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\OrderService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\OrderService
 */
final class OrderServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return OrderService::class;
    }

    /**
     * @covers ::create
     */
    public function testCreate(): void
    {
        $this->quickTest(
            'create',
            [
                [
                    'buyer' => [
                        'first_name' => 'Иван',
                        'last_name'  => 'Иванов',
                        'phone'      => '+79001234567',
                        // must be filtered out
                        'foo'        => 'bar',
                    ],
                    'recipient' => [
                        'recipient_first_name' => 'Пётр',
                        'recipient_last_name'  => 'Петров',
                        'recipient_phone'      => '+79007654321',
                    ],
                    'delivery'        => ['courier' => ['address' => 'Москва']],
                    'delivery_schema' => 'FBS',
                    'splits'          => [['items' => []]],
                    // must be filtered out
                    'baz'             => 'qux',
                ],
            ],
            [
                'POST',
                '/v2/order/create',
                '{"buyer":{"first_name":"Иван","last_name":"Иванов","phone":"+79001234567"},"recipient":{"recipient_first_name":"Пётр","recipient_last_name":"Петров","recipient_phone":"+79007654321"},"delivery":{"courier":{"address":"Москва"}},"delivery_schema":"FBS","splits":[{"items":[]}]}',
            ],
            '{"order_number":"33920474-0032","postings":["33920474-0032-1"]}',
            static function (array $result): void {
                self::assertSame('33920474-0032', $result['order_number']);
            }
        );
    }
}
