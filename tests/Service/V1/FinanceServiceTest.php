<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\FinanceService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

class FinanceServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return FinanceService::class;
    }

    public function testRealization(): void
    {
        $this->quickTest(
            'realization',
            [
                ['date' => '2022-02'],
            ],
            [
                'POST',
                '/v1/finance/realization',
                '{"date":"2022-02"}',
            ]
        );
    }

    public function testRealizationByDay(): void
    {
        $json = '{"rows":[{"rowNumber":1,"seller_price_per_instance":1000.0}]}';

        $this->quickTest(
            'realizationByDay',
            [2026, 8, 1],
            [
                'POST',
                '/v1/finance/realization/by-day',
                '{"year":2026,"month":8,"day":1}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    public function testRealizationPosting(): void
    {
        $json = '{"header":{"number":"1234"},"rows":[]}';

        $this->quickTest(
            'realizationPosting',
            [2026, 8],
            [
                'POST',
                '/v1/finance/realization/posting',
                '{"year":2026,"month":8}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    public function testBalance(): void
    {
        $json = '{"total":{"opening_balance":{"currency_code":"RUB","value":100.0}}}';

        $this->quickTest(
            'balance',
            ['2026-08-01', '2026-08-31'],
            [
                'POST',
                '/v1/finance/balance',
                '{"date_from":"2026-08-01","date_to":"2026-08-31"}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    public function testCashFlowStatementList(): void
    {
        $this->quickTest(
            'cashFlowStatementList',
            [
                [
                    'date' => [
                        'from' => '2026-08-01T00:00:00.000Z',
                        'to'   => '2026-08-31T00:00:00.000Z',
                        // must be filtered out
                        'foo'  => 'bar',
                    ],
                    'page'         => '2',
                    'page_size'    => '50',
                    'with_details' => true,
                ],
            ],
            [
                'POST',
                '/v1/finance/cash-flow-statement/list',
                '{"page":2,"page_size":50,"date":{"from":"2026-08-01T00:00:00.000Z","to":"2026-08-31T00:00:00.000Z"},"with_details":true}',
            ],
            '{"result":{"page_count":1}}'
        );
    }

    public function testAccrualTypes(): void
    {
        $json = '{"accrual_types":[{"id":1,"name":"MarketplaceServiceItemFulfillment"}]}';

        $this->quickTest(
            'accrualTypes',
            [],
            ['POST', '/v1/finance/accrual/types', '{}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    public function testAccrualByDay(): void
    {
        $json = '{"accruals":[],"last_id":"next-id"}';

        $this->quickTest(
            'accrualByDay',
            ['2026-08-01', 'prev-id'],
            [
                'POST',
                '/v1/finance/accrual/by-day',
                '{"date":"2026-08-01","last_id":"prev-id"}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    public function testAccrualPostings(): void
    {
        $json = '{"posting_accruals":[{"posting_number":"33920474-0032-1","accruals":[]}]}';

        $this->quickTest(
            'accrualPostings',
            [['33920474-0032-1']],
            [
                'POST',
                '/v1/finance/accrual/postings',
                '{"posting_numbers":["33920474-0032-1"]}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    public function testDocumentB2bSales(): void
    {
        $this->quickTest(
            'documentB2bSales',
            ['2026-08', 'RU'],
            [
                'POST',
                '/v1/finance/document-b2b-sales',
                '{"date":"2026-08","language":"RU"}',
            ],
            '{"result":{"code":"report-code"}}'
        );
    }

    public function testDocumentB2bSalesJson(): void
    {
        $json = '{"date_from":"2026-08-01","date_to":"2026-08-31","invoices":[]}';

        $this->quickTest(
            'documentB2bSalesJson',
            ['2026-08'],
            [
                'POST',
                '/v1/finance/document-b2b-sales/json',
                '{"date":"2026-08"}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    public function testMutualSettlement(): void
    {
        $this->quickTest(
            'mutualSettlement',
            ['2026-08'],
            [
                'POST',
                '/v1/finance/mutual-settlement',
                '{"date":"2026-08","language":"DEFAULT"}',
            ],
            '{"result":{"code":"report-code"}}'
        );
    }

    public function testCompensation(): void
    {
        $this->quickTest(
            'compensation',
            ['2026-08'],
            [
                'POST',
                '/v1/finance/compensation',
                '{"date":"2026-08","language":"DEFAULT"}',
            ],
            '{"result":{"code":"report-code"}}'
        );
    }

    public function testDecompensation(): void
    {
        $this->quickTest(
            'decompensation',
            ['2026-08'],
            [
                'POST',
                '/v1/finance/decompensation',
                '{"date":"2026-08","language":"DEFAULT"}',
            ],
            '{"result":{"code":"report-code"}}'
        );
    }

    public function testProductsBuyout(): void
    {
        $json = '{"products":[]}';

        $this->quickTest(
            'productsBuyout',
            ['2026-08-01T00:00:00.000Z', '2026-08-31T00:00:00.000Z'],
            [
                'POST',
                '/v1/finance/products/buyout',
                '{"date_from":"2026-08-01T00:00:00.000Z","date_to":"2026-08-31T00:00:00.000Z"}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }
}
