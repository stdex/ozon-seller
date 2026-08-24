<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\PricingStrategyService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\PricingStrategyService
 */
final class PricingStrategyServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return PricingStrategyService::class;
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $this->quickTest(
            'list',
            [2, 50],
            ['POST', '/v1/pricing-strategy/list', '{"page":2,"limit":50}'],
            '{"strategies":[],"total":0}',
            static function (array $result): void {
                self::assertSame(['strategies' => [], 'total' => 0], $result);
            }
        );
    }

    /**
     * @covers ::competitorsList
     */
    public function testCompetitorsList(): void
    {
        $this->quickTest(
            'competitorsList',
            [],
            ['POST', '/v1/pricing-strategy/competitors/list', '{"page":1,"limit":100}'],
            '{"competitor":[],"total":0}',
            static function (array $result): void {
                self::assertSame(['competitor' => [], 'total' => 0], $result);
            }
        );
    }

    /**
     * @covers ::create
     */
    public function testCreate(): void
    {
        $this->quickTest(
            'create',
            [
                'Моя стратегия',
                [
                    [
                        'competitor_id' => '1',
                        'coefficient'   => '0.9',
                        // must be filtered out
                        'foo'           => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/pricing-strategy/create',
                '{"strategy_name":"Моя стратегия","competitors":[{"competitor_id":1,"coefficient":0.9}]}',
            ],
            '{"result":{"strategy_id":"strategy-1"}}'
        );
    }

    /**
     * @covers ::info
     */
    public function testInfo(): void
    {
        $this->quickTest(
            'info',
            ['strategy-1'],
            ['POST', '/v1/pricing-strategy/info', '{"strategy_id":"strategy-1"}'],
            '{"result":{"name":"Моя стратегия","enabled":true}}'
        );
    }

    /**
     * @covers ::update
     */
    public function testUpdate(): void
    {
        $this->quickTest(
            'update',
            ['strategy-1', 'Новое имя', [['competitor_id' => 1, 'coefficient' => 1.1]]],
            [
                'POST',
                '/v1/pricing-strategy/update',
                '{"strategy_id":"strategy-1","strategy_name":"Новое имя","competitors":[{"competitor_id":1,"coefficient":1.1}]}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::delete
     */
    public function testDelete(): void
    {
        $this->quickTest(
            'delete',
            ['strategy-1'],
            ['POST', '/v1/pricing-strategy/delete', '{"strategy_id":"strategy-1"}'],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::status
     */
    public function testStatus(): void
    {
        $this->quickTest(
            'status',
            ['strategy-1', false],
            [
                'POST',
                '/v1/pricing-strategy/status',
                '{"strategy_id":"strategy-1","enabled":false}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::productsAdd
     */
    public function testProductsAdd(): void
    {
        $this->quickTest(
            'productsAdd',
            ['strategy-1', [123456]],
            [
                'POST',
                '/v1/pricing-strategy/products/add',
                '{"strategy_id":"strategy-1","product_id":["123456"]}',
            ],
            '{"result":{"failed_product_count":0}}'
        );
    }

    /**
     * @covers ::productsList
     */
    public function testProductsList(): void
    {
        $this->quickTest(
            'productsList',
            ['strategy-1'],
            ['POST', '/v1/pricing-strategy/products/list', '{"strategy_id":"strategy-1"}'],
            '{"result":{"product_id":["123456"]}}'
        );
    }

    /**
     * @covers ::productsDelete
     */
    public function testProductsDelete(): void
    {
        $this->quickTest(
            'productsDelete',
            [[123456]],
            [
                'POST',
                '/v1/pricing-strategy/products/delete',
                '{"product_id":["123456"]}',
            ],
            '{"result":{"failed_product_count":0}}'
        );
    }

    /**
     * @covers ::productInfo
     */
    public function testProductInfo(): void
    {
        $this->quickTest(
            'productInfo',
            [123456],
            ['POST', '/v1/pricing-strategy/product/info', '{"product_id":123456}'],
            '{"result":{"strategy_id":"strategy-1","is_enabled":true}}'
        );
    }

    /**
     * @covers ::strategyIdsByProductIds
     */
    public function testStrategyIdsByProductIds(): void
    {
        $this->quickTest(
            'strategyIdsByProductIds',
            [[123456]],
            [
                'POST',
                '/v1/pricing-strategy/strategy-ids-by-product-ids',
                '{"product_id":["123456"]}',
            ],
            '{"result":{"products_info":[]}}'
        );
    }
}
