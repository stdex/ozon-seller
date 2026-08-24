<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V5\Posting;

use Gam6itko\OzonSeller\Service\V5\Posting\FbsService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V5\Posting\FbsService
 */
final class FbsServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return FbsService::class;
    }

    /**
     * @covers ::productExemplarStatus
     */
    public function testProductExemplarStatus(): void
    {
        $this->quickTest(
            'productExemplarStatus',
            ['33920474-0032-1'],
            [
                'POST',
                '/v5/fbs/posting/product/exemplar/status',
                '{"posting_number":"33920474-0032-1"}',
            ],
            '{"posting_number":"33920474-0032-1","status":"ship_available"}',
            static function (array $result): void {
                self::assertSame('ship_available', $result['status']);
            }
        );
    }

    /**
     * @covers ::productExemplarValidate
     */
    public function testProductExemplarValidate(): void
    {
        $this->quickTest(
            'productExemplarValidate',
            [
                '33920474-0032-1',
                [
                    [
                        'product_id' => 160249683,
                        'exemplars'  => [['gtd' => '', 'marking_code' => 'code-1']],
                        // must be filtered out
                        'foo'        => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v5/fbs/posting/product/exemplar/validate',
                '{"posting_number":"33920474-0032-1","products":[{"product_id":160249683,"exemplars":[{"gtd":"","marking_code":"code-1"}]}]}',
            ],
            '{"products":[]}',
            static function (array $result): void {
                self::assertSame(['products' => []], $result);
            }
        );
    }
}
