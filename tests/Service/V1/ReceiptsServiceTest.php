<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\ReceiptsService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\ReceiptsService
 */
final class ReceiptsServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ReceiptsService::class;
    }

    /**
     * @covers ::sellerList
     */
    public function testSellerList(): void
    {
        $this->quickTest(
            'sellerList',
            [
                [
                    'page'            => '2',
                    'page_size'       => '50',
                    'posting_numbers' => ['33920474-0032-1'],
                    // must be filtered out
                    'foo'             => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/receipts/seller/list',
                '{"page":2,"page_size":50,"posting_numbers":["33920474-0032-1"]}',
            ],
            '{"receipts":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['receipts' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::sellerList
     */
    public function testSellerListDefaults(): void
    {
        $this->quickTest(
            'sellerList',
            [],
            ['POST', '/v1/receipts/seller/list', '{"page":1,"page_size":100}'],
            '{"receipts":[]}',
            static function (array $result): void {
                self::assertSame(['receipts' => []], $result);
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
            ['019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8'],
            [
                'POST',
                '/v1/receipts/get',
                '{"receipt_id":"019a2b3c-4d5e-6f70-8192-a3b4c5d6e7f8"}',
            ],
            '{"content":"JVBERi0="}',
            static function (array $result): void {
                self::assertSame(['content' => 'JVBERi0='], $result);
            }
        );
    }
}
