<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\AssemblyService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\AssemblyService
 */
final class AssemblyServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return AssemblyService::class;
    }

    /**
     * @covers ::fbsPostingList
     */
    public function testFbsPostingList(): void
    {
        $this->quickTest(
            'fbsPostingList',
            [
                [
                    'filter' => [
                        'cutoff_from'        => '2026-08-25T00:00:00Z',
                        'cutoff_to'          => '2026-08-26T00:00:00Z',
                        'delivery_method_id' => '123',
                        // must be filtered out
                        'foo'                => 'bar',
                    ],
                    'limit'    => '50',
                    'cursor'   => 'prev-cursor',
                    'sort_dir' => 'DESC',
                ],
            ],
            [
                'POST',
                '/v1/assembly/fbs/posting/list',
                '{"limit":50,"sort_dir":"DESC","filter":{"cutoff_from":"2026-08-25T00:00:00Z","cutoff_to":"2026-08-26T00:00:00Z","delivery_method_id":123},"cursor":"prev-cursor"}',
            ],
            '{"postings":[],"cursor":""}',
            static function (array $result): void {
                self::assertSame(['postings' => [], 'cursor' => ''], $result);
            }
        );
    }

    /**
     * @covers ::fbsProductList
     */
    public function testFbsProductList(): void
    {
        $this->quickTest(
            'fbsProductList',
            [
                [
                    'filter' => [
                        'cutoff_from' => '2026-08-25T00:00:00Z',
                        'cutoff_to'   => '2026-08-26T00:00:00Z',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/assembly/fbs/product/list',
                '{"limit":100,"offset":0,"sort_dir":"ASC","filter":{"cutoff_from":"2026-08-25T00:00:00Z","cutoff_to":"2026-08-26T00:00:00Z"}}',
            ],
            '{"products":[],"products_count":0,"has_next":false}',
            static function (array $result): void {
                self::assertSame(['products' => [], 'products_count' => 0, 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::carriagePostingList
     */
    public function testCarriagePostingList(): void
    {
        $this->quickTest(
            'carriagePostingList',
            [
                [
                    'filter' => [
                        'carriage_id'        => '1',
                        'cutoff_from'        => '2026-08-25T00:00:00Z',
                        'delivery_method_id' => '123',
                        // must be filtered out
                        'foo'                => 'bar',
                    ],
                    'limit' => '50',
                ],
            ],
            [
                'POST',
                '/v1/assembly/carriage/posting/list',
                '{"limit":50,"filter":{"carriage_id":1,"cutoff_from":"2026-08-25T00:00:00Z","delivery_method_id":123}}',
            ],
            '{"postings":[],"can_print_mass_label":true}',
            static function (array $result): void {
                self::assertSame(['postings' => [], 'can_print_mass_label' => true], $result);
            }
        );
    }

    /**
     * @covers ::carriageProductList
     */
    public function testCarriageProductList(): void
    {
        $this->quickTest(
            'carriageProductList',
            [['filter' => ['carriage_id' => 1]]],
            [
                'POST',
                '/v1/assembly/carriage/product/list',
                '{"limit":100,"filter":{"carriage_id":1}}',
            ],
            '{"products":[]}',
            static function (array $result): void {
                self::assertSame(['products' => []], $result);
            }
        );
    }
}
