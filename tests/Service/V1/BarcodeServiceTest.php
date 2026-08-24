<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\BarcodeService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\BarcodeService
 */
final class BarcodeServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return BarcodeService::class;
    }

    /**
     * @covers ::add
     */
    public function testAdd(): void
    {
        $this->quickTest(
            'add',
            [
                [
                    [
                        'barcode' => '112233445566',
                        'sku'     => '160249683',
                        // must be filtered out
                        'foo'     => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/barcode/add',
                '{"barcodes":[{"barcode":"112233445566","sku":160249683}]}',
            ],
            '{"errors":[]}',
            static function (array $result): void {
                self::assertSame(['errors' => []], $result);
            }
        );
    }

    /**
     * @covers ::generate
     */
    public function testGenerate(): void
    {
        $this->quickTest(
            'generate',
            [[123456, '654321']],
            [
                'POST',
                '/v1/barcode/generate',
                '{"product_ids":["123456","654321"]}',
            ],
            '{"errors":[]}',
            static function (array $result): void {
                self::assertSame(['errors' => []], $result);
            }
        );
    }
}
