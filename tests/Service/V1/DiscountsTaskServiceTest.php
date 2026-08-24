<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\DiscountsTaskService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\DiscountsTaskService
 */
final class DiscountsTaskServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return DiscountsTaskService::class;
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $this->quickTest(
            'list',
            ['NEW', 2, 50],
            [
                'POST',
                '/v1/actions/discounts-task/list',
                '{"status":"NEW","page":2,"limit":50}',
            ],
            '{"result":[]}'
        );
    }

    /**
     * @covers ::approve
     */
    public function testApprove(): void
    {
        $this->quickTest(
            'approve',
            [
                [
                    [
                        'id'                    => '123',
                        'approved_price'        => '900.5',
                        'approved_quantity_min' => '1',
                        'approved_quantity_max' => '5',
                        'seller_comment'        => 'ок',
                        // must be filtered out
                        'foo'                   => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/actions/discounts-task/approve',
                '{"tasks":[{"id":123,"approved_price":900.5,"approved_quantity_min":1,"approved_quantity_max":5,"seller_comment":"ок"}]}',
            ],
            '{"result":{"success_count":1,"fail_count":0}}'
        );
    }

    /**
     * @covers ::decline
     */
    public function testDecline(): void
    {
        $this->quickTest(
            'decline',
            [[['id' => 123, 'seller_comment' => 'нет']]],
            [
                'POST',
                '/v1/actions/discounts-task/decline',
                '{"tasks":[{"id":123,"seller_comment":"нет"}]}',
            ],
            '{"result":{"success_count":1}}'
        );
    }

    /**
     * @covers ::autoAddProductsList
     */
    public function testAutoAddProductsList(): void
    {
        $this->quickTest(
            'autoAddProductsList',
            [1, '2026-09-01'],
            [
                'POST',
                '/v1/actions/auto-add/products/list',
                '{"action_id":1,"auto_add_date":"2026-09-01","limit":100,"offset":0}',
            ],
            '{"products":[],"total":0}',
            static function (array $result): void {
                self::assertSame(['products' => [], 'total' => 0], $result);
            }
        );
    }

    /**
     * @covers ::autoAddProductsCandidates
     */
    public function testAutoAddProductsCandidates(): void
    {
        $this->quickTest(
            'autoAddProductsCandidates',
            [1, '2026-09-01', 50, 10],
            [
                'POST',
                '/v1/actions/auto-add/products/candidates',
                '{"action_id":1,"auto_add_date":"2026-09-01","limit":50,"offset":10}',
            ],
            '{"products":[]}',
            static function (array $result): void {
                self::assertSame(['products' => []], $result);
            }
        );
    }

    /**
     * @covers ::autoAddProductsDelete
     */
    public function testAutoAddProductsDelete(): void
    {
        $this->quickTest(
            'autoAddProductsDelete',
            [1, '2026-09-01', [123456]],
            [
                'POST',
                '/v1/actions/auto-add/products/delete',
                '{"action_id":1,"auto_add_date":"2026-09-01","product_ids":["123456"]}',
            ],
            '{"product_ids":["123456"]}',
            static function (array $result): void {
                self::assertSame(['product_ids' => ['123456']], $result);
            }
        );
    }

    /**
     * @covers ::autoAddProductsUpdate
     */
    public function testAutoAddProductsUpdate(): void
    {
        $this->quickTest(
            'autoAddProductsUpdate',
            [
                1,
                '2026-09-01',
                [
                    [
                        'product_id'   => '123456',
                        'action_price' => '900.5',
                        'quantity'     => '10',
                        'currency'     => 'RUB',
                        // must be filtered out
                        'foo'          => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/actions/auto-add/products/update',
                '{"action_id":1,"auto_add_date":"2026-09-01","to_update":[{"product_id":123456,"action_price":900.5,"quantity":10,"currency":"RUB"}]}',
            ],
            '{"updated_ids":["123456"]}',
            static function (array $result): void {
                self::assertSame(['updated_ids' => ['123456']], $result);
            }
        );
    }
}
