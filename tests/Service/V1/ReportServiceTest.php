<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Enum\TransactionType;
use Gam6itko\OzonSeller\Service\V1\ReportService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

class ReportServiceTest extends AbstractTestCase
{
    public function getClass(): string
    {
        return ReportService::class;
    }

    public function testList(): void
    {
        $query = [
            'page'        => 1,
            'page_size'   => 100,
            'report_type' => 'SELLER_TRANSACTIONS',
        ];
        $this->quickTest(
            'list',
            [$query],
            [
                'POST',
                '/v1/report/list',
                '{"page":1,"page_size":100,"report_type":"SELLER_TRANSACTIONS"}',
            ]
        );
    }

    public function testCode(): void
    {
        $this->quickTest(
            'info',
            ['63d60fd4-1959-4087-89fa-2afa320eb2fb'],
            [
                'POST',
                '/v1/report/info',
                '{"code":"63d60fd4-1959-4087-89fa-2afa320eb2fb"}',
            ]
        );
    }

    public function testProducts(): void
    {
        $query = [
            'offer_id'   => ['GJ5O52T5'],
            'search'     => 'SAMSUNG',
            'sku'        => [555929582],
            'visibility' => 'VISIBLE',
        ];

        $this->quickTest(
            'products',
            [$query],
            [
                'POST',
                '/v1/report/products/create',
                '{"offer_id":["GJ5O52T5"],"search":"SAMSUNG","sku":[555929582],"visibility":"VISIBLE"}',
            ]
        );
    }

    public function testTransaction(): void
    {
        $this->quickTest(
            'transaction',
            [new \DateTime('2019-01-01'), new \DateTime('2019-01-15'), 'MEIZU', TransactionType::ALL],
            [
                'POST',
                '/v1/report/transactions/create',
                '{"date_from":"2019-01-01","date_to":"2019-01-15","search":"MEIZU","transaction_type":"ALL"}',
            ]
        );
    }

    public function testPostingsCreate(): void
    {
        $this->quickTest(
            'postingsCreate',
            [
                [
                    'filter' => [
                        'processed_at_from' => '2026-08-01T00:00:00.000Z',
                        'processed_at_to'   => '2026-08-08T00:00:00.000Z',
                        'delivery_schema'   => ['fbs'],
                        'sku'               => ['160249683'],
                        'statuses'          => ['1'],
                        'is_express'        => false,
                        // must be filtered out
                        'foo'               => 'bar',
                    ],
                    'with' => [
                        'analytics_data' => true,
                        // must be filtered out
                        'foo'            => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/report/postings/create',
                '{"language":"DEFAULT","filter":{"processed_at_from":"2026-08-01T00:00:00.000Z","processed_at_to":"2026-08-08T00:00:00.000Z","delivery_schema":["fbs"],"is_express":false,"sku":[160249683],"statuses":[1]},"with":{"analytics_data":true}}',
            ],
            '{"result":{"code":"report-code"}}'
        );
    }

    public function testDiscountedCreate(): void
    {
        $this->quickTest(
            'discountedCreate',
            [],
            ['POST', '/v1/report/discounted/create', '{}'],
            '{"code":"report-code"}',
            static function (array $result): void {
                self::assertSame(['code' => 'report-code'], $result);
            }
        );
    }

    public function testWarehouseStock(): void
    {
        $this->quickTest(
            'warehouseStock',
            [[123, '456'], 'RU'],
            [
                'POST',
                '/v1/report/warehouse/stock',
                '{"warehouseId":["123","456"],"language":"RU"}',
            ],
            '{"result":{"code":"report-code"}}'
        );
    }

    public function testPlacementByProductsCreate(): void
    {
        $this->quickTest(
            'placementByProductsCreate',
            ['2026-08-01', '2026-08-08'],
            [
                'POST',
                '/v1/report/placement/by-products/create',
                '{"date_from":"2026-08-01","date_to":"2026-08-08"}',
            ],
            '{"code":"report-code"}',
            static function (array $result): void {
                self::assertSame(['code' => 'report-code'], $result);
            }
        );
    }

    public function testPlacementBySuppliesCreate(): void
    {
        $this->quickTest(
            'placementBySuppliesCreate',
            ['2026-08-01', '2026-08-08'],
            [
                'POST',
                '/v1/report/placement/by-supplies/create',
                '{"date_from":"2026-08-01","date_to":"2026-08-08"}',
            ],
            '{"code":"report-code"}',
            static function (array $result): void {
                self::assertSame(['code' => 'report-code'], $result);
            }
        );
    }

    public function testMarkedProductsSalesCreate(): void
    {
        $this->quickTest(
            'markedProductsSalesCreate',
            ['2026-08-01', '2026-08-08'],
            [
                'POST',
                '/v1/report/marked-products-sales/create',
                '{"date":{"from":"2026-08-01","to":"2026-08-08"}}',
            ],
            '{"result":{"code":"report-code"}}'
        );
    }

    public function testRealizationPostingCreate(): void
    {
        $this->quickTest(
            'realizationPostingCreate',
            [2026, 8],
            [
                'POST',
                '/v1/report/realization/posting/create',
                '{"year":2026,"month":8}',
            ],
            '{"code":"report-code"}',
            static function (array $result): void {
                self::assertSame(['code' => 'report-code'], $result);
            }
        );
    }
}
