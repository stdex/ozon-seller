<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V3;

use Gam6itko\OzonSeller\Service\V3\FinanceService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V3\FinanceService
 */
final class FinanceServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return FinanceService::class;
    }

    /**
     * @covers ::transactionList
     */
    public function testTransactionList(): void
    {
        $this->quickTest(
            'transactionList',
            [
                [
                    'filter' => [
                        'date' => [
                            'from' => '2026-08-01T00:00:00.000Z',
                            'to'   => '2026-08-31T00:00:00.000Z',
                            // must be filtered out
                            'foo'  => 'bar',
                        ],
                        'operation_type'   => ['ClientReturnAgentOperation'],
                        'posting_number'   => '33920474-0032-1',
                        'transaction_type' => 'all',
                        // must be filtered out
                        'bar'              => 'baz',
                    ],
                    'page'      => '2',
                    'page_size' => '50',
                ],
            ],
            [
                'POST',
                '/v3/finance/transaction/list',
                '{"page":2,"page_size":50,"filter":{"date":{"from":"2026-08-01T00:00:00.000Z","to":"2026-08-31T00:00:00.000Z"},"operation_type":["ClientReturnAgentOperation"],"posting_number":"33920474-0032-1","transaction_type":"all"}}',
            ],
            '{"result":{"operations":[],"page_count":1,"row_count":0}}'
        );
    }

    /**
     * @covers ::transactionList
     */
    public function testTransactionListDefaults(): void
    {
        $this->quickTest(
            'transactionList',
            [],
            [
                'POST',
                '/v3/finance/transaction/list',
                '{"page":1,"page_size":100}',
            ],
            '{"result":{"operations":[]}}'
        );
    }

    /**
     * @covers ::transactionTotals
     */
    public function testTransactionTotals(): void
    {
        $this->quickTest(
            'transactionTotals',
            [
                [
                    'date' => [
                        'from' => '2026-08-01T00:00:00.000Z',
                        'to'   => '2026-08-31T00:00:00.000Z',
                    ],
                    'transaction_type' => 'all',
                ],
            ],
            [
                'POST',
                '/v3/finance/transaction/totals',
                '{"date":{"from":"2026-08-01T00:00:00.000Z","to":"2026-08-31T00:00:00.000Z"},"transaction_type":"all"}',
            ],
            '{"result":{"accruals_for_sale":1000.0}}'
        );
    }

    /**
     * @covers ::transactionTotals
     */
    public function testTransactionTotalsEmpty(): void
    {
        $this->quickTest(
            'transactionTotals',
            [],
            ['POST', '/v3/finance/transaction/totals', '{}'],
            '{"result":{}}'
        );
    }
}
