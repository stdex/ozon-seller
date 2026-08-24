<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\SellerActionsService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\SellerActionsService
 */
final class SellerActionsServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return SellerActionsService::class;
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
                    'action_ids'  => [1],
                    'action_type' => ['DISCOUNT'],
                    'status'      => ['ACTIVE'],
                    'search'      => 'акция',
                    'limit'       => '50',
                    'offset'      => '10',
                    // must be filtered out
                    'foo'         => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/seller-actions/list',
                '{"limit":50,"offset":10,"action_ids":["1"],"action_type":["DISCOUNT"],"status":["ACTIVE"],"search":"акция"}',
            ],
            '{"actions":[],"total":0}',
            static function (array $result): void {
                self::assertSame(['actions' => [], 'total' => 0], $result);
            }
        );
    }

    /**
     * @covers ::archive
     */
    public function testArchive(): void
    {
        $this->quickTest(
            'archive',
            [1],
            ['POST', '/v1/seller-actions/archive', '{"action_id":1}'],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::changeActivity
     */
    public function testChangeActivity(): void
    {
        $this->quickTest(
            'changeActivity',
            [1, false],
            [
                'POST',
                '/v1/seller-actions/change-activity',
                '{"action_id":1,"is_turn_on":false}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::voucherGet
     */
    public function testVoucherGet(): void
    {
        $this->quickTest(
            'voucherGet',
            [1],
            ['POST', '/v1/seller-actions/voucher/get', '{"action_id":1}'],
            '{"file":"Y29kZQ=="}',
            static function (array $result): void {
                self::assertSame(['file' => 'Y29kZQ=='], $result);
            }
        );
    }

    /**
     * @covers ::productsList
     */
    public function testProductsList(): void
    {
        $this->quickTest(
            'productsList',
            [1, 50, 10],
            [
                'POST',
                '/v1/seller-actions/products/list',
                '{"action_id":1,"limit":50,"cursor":10}',
            ],
            '{"products":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['products' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::productsCandidates
     */
    public function testProductsCandidates(): void
    {
        $this->quickTest(
            'productsCandidates',
            [1],
            [
                'POST',
                '/v1/seller-actions/products/candidates',
                '{"action_id":1,"limit":100}',
            ],
            '{"products":[]}',
            static function (array $result): void {
                self::assertSame(['products' => []], $result);
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
            [
                1,
                [
                    [
                        'sku'              => '160249683',
                        'discount_percent' => '10',
                        'currency'         => 'RUB',
                        // must be filtered out
                        'foo'              => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/seller-actions/products/add',
                '{"action_id":1,"products":[{"sku":160249683,"discount_percent":10,"currency":"RUB"}]}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::productsDelete
     */
    public function testProductsDelete(): void
    {
        $this->quickTest(
            'productsDelete',
            [1, [160249683]],
            [
                'POST',
                '/v1/seller-actions/products/delete',
                '{"action_id":1,"skus":["160249683"]}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::createDiscount
     */
    public function testCreateDiscount(): void
    {
        $this->quickTest(
            'createDiscount',
            [
                [
                    'date_start'         => '2026-09-01T00:00:00Z',
                    'date_end'           => '2026-09-30T00:00:00Z',
                    'min_action_percent' => '5',
                    'title'              => 'Сентябрьская скидка',
                    // must be filtered out
                    'foo'                => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/seller-actions/create/discount',
                '{"date_start":"2026-09-01T00:00:00Z","date_end":"2026-09-30T00:00:00Z","min_action_percent":5,"title":"Сентябрьская скидка"}',
            ],
            '{"action_id":1}',
            static function (array $result): void {
                self::assertSame(['action_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::createDiscountWithCondition
     */
    public function testCreateDiscountWithCondition(): void
    {
        $this->quickTest(
            'createDiscountWithCondition',
            [
                [
                    'date_start'       => '2026-09-01T00:00:00Z',
                    'date_end'         => '2026-09-30T00:00:00Z',
                    'discount_type'    => 'PERCENT',
                    'discount_value'   => '10',
                    'min_order_amount' => '1000',
                ],
            ],
            [
                'POST',
                '/v1/seller-actions/create/discount-with-condition',
                '{"date_start":"2026-09-01T00:00:00Z","date_end":"2026-09-30T00:00:00Z","discount_type":"PERCENT","discount_value":10,"min_order_amount":1000}',
            ],
            '{"action_id":1}',
            static function (array $result): void {
                self::assertSame(['action_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::createInstallment
     */
    public function testCreateInstallment(): void
    {
        $this->quickTest(
            'createInstallment',
            ['2026-09-01T00:00:00Z', 'Рассрочка'],
            [
                'POST',
                '/v1/seller-actions/create/installment',
                '{"date_start":"2026-09-01T00:00:00Z","title":"Рассрочка"}',
            ],
            '{"action_id":1}',
            static function (array $result): void {
                self::assertSame(['action_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::createMultiLevelDiscount
     */
    public function testCreateMultiLevelDiscount(): void
    {
        $this->quickTest(
            'createMultiLevelDiscount',
            [
                [
                    'date_start'      => '2026-09-01T00:00:00Z',
                    'date_end'        => '2026-09-30T00:00:00Z',
                    'discount_type'   => 'CURRENCY',
                    'discount_levels' => [
                        [
                            'order_amount'   => '1000',
                            'discount_value' => '100',
                            // must be filtered out
                            'foo'            => 'bar',
                        ],
                    ],
                    'is_legal_entities_segment' => false,
                ],
            ],
            [
                'POST',
                '/v1/seller-actions/create/multi-level-discount',
                '{"date_start":"2026-09-01T00:00:00Z","date_end":"2026-09-30T00:00:00Z","discount_type":"CURRENCY","discount_levels":[{"order_amount":1000,"discount_value":100}],"is_legal_entities_segment":false}',
            ],
            '{"action_id":1}',
            static function (array $result): void {
                self::assertSame(['action_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::createVoucher
     */
    public function testCreateVoucher(): void
    {
        $this->quickTest(
            'createVoucher',
            [
                [
                    'date_start'         => '2026-09-01T00:00:00Z',
                    'date_end'           => '2026-09-30T00:00:00Z',
                    'discount_type'      => 'PERCENT',
                    'discount_value'     => '15',
                    'budget'             => '100000',
                    'title'              => 'Промокод',
                    'voucher_parameters' => [
                        'count_codes' => '100',
                        'is_private'  => true,
                        'type'        => 'UNIQUE',
                        // must be filtered out
                        'foo'         => 'bar',
                    ],
                    'user_ids' => [123],
                ],
            ],
            [
                'POST',
                '/v1/seller-actions/create/voucher',
                '{"date_start":"2026-09-01T00:00:00Z","date_end":"2026-09-30T00:00:00Z","discount_type":"PERCENT","discount_value":15,"budget":100000,"title":"Промокод","voucher_parameters":{"count_codes":100,"is_private":true,"type":"UNIQUE"},"user_ids":["123"]}',
            ],
            '{"action_id":1}',
            static function (array $result): void {
                self::assertSame(['action_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::updateDiscount
     */
    public function testUpdateDiscount(): void
    {
        $this->quickTest(
            'updateDiscount',
            [
                1,
                [
                    'date_start' => '2026-09-01T00:00:00Z',
                    'date_end'   => '2026-09-30T00:00:00Z',
                    'title'      => 'Новое имя',
                    // must be filtered out
                    'foo'        => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/seller-actions/update/discount',
                '{"action_id":1,"action_parameters":{"date_start":"2026-09-01T00:00:00Z","date_end":"2026-09-30T00:00:00Z","title":"Новое имя"}}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::updateDiscountWithCondition
     */
    public function testUpdateDiscountWithCondition(): void
    {
        $this->quickTest(
            'updateDiscountWithCondition',
            [
                1,
                [
                    'date_start'       => '2026-09-01T00:00:00Z',
                    'date_end'         => '2026-09-30T00:00:00Z',
                    'discount_value'   => '20',
                    'min_order_amount' => '2000',
                    'title'            => 'Новое имя',
                ],
            ],
            [
                'POST',
                '/v1/seller-actions/update/discount-with-condition',
                '{"action_id":1,"action_parameters":{"date_start":"2026-09-01T00:00:00Z","date_end":"2026-09-30T00:00:00Z","discount_value":20,"min_order_amount":2000,"title":"Новое имя"}}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::updateInstallment
     */
    public function testUpdateInstallment(): void
    {
        $this->quickTest(
            'updateInstallment',
            [1, ['date_start' => '2026-09-01T00:00:00Z', 'title' => 'Рассрочка']],
            [
                'POST',
                '/v1/seller-actions/update/installment',
                '{"action_id":1,"action_parameters":{"date_start":"2026-09-01T00:00:00Z","title":"Рассрочка"}}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::updateMultiLevelDiscount
     */
    public function testUpdateMultiLevelDiscount(): void
    {
        $this->quickTest(
            'updateMultiLevelDiscount',
            [
                1,
                [
                    'date_start'      => '2026-09-01T00:00:00Z',
                    'date_end'        => '2026-09-30T00:00:00Z',
                    'discount_levels' => [['order_amount' => 1000, 'discount_value' => 50]],
                    'title'           => 'Новое имя',
                ],
            ],
            [
                'POST',
                '/v1/seller-actions/update/multi-level-discount',
                '{"action_id":1,"action_parameters":{"date_start":"2026-09-01T00:00:00Z","date_end":"2026-09-30T00:00:00Z","discount_levels":[{"order_amount":1000,"discount_value":50}],"title":"Новое имя"}}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::updateVoucher
     */
    public function testUpdateVoucher(): void
    {
        $this->quickTest(
            'updateVoucher',
            [
                1,
                [
                    'date_start'     => '2026-09-01T00:00:00Z',
                    'date_end'       => '2026-09-30T00:00:00Z',
                    'discount_value' => '25',
                    'budget'         => '200000',
                    'title'          => 'Промокод',
                    'user_ids'       => [123],
                ],
            ],
            [
                'POST',
                '/v1/seller-actions/update/voucher',
                '{"action_id":1,"action_parameters":{"date_start":"2026-09-01T00:00:00Z","date_end":"2026-09-30T00:00:00Z","discount_value":25,"budget":200000,"title":"Промокод","user_ids":["123"]}}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }
}
