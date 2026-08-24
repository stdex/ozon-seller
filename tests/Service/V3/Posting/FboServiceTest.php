<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V3\Posting;

use Gam6itko\OzonSeller\Service\V3\Posting\FboService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V3\Posting\FboService
 */
final class FboServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return FboService::class;
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
                    'filter' => [
                        'since' => '2026-08-01T00:00:00+00:00',
                        'to'    => '2026-08-08T00:00:00+00:00',
                    ],
                ],
            ],
            [
                'POST',
                '/v3/posting/fbo/list',
                '{"filter":{"since":"2026-08-01T00:00:00+00:00","to":"2026-08-08T00:00:00+00:00"},"cursor":"","limit":10,"sort_dir":"asc","translit":true,"with":{"analytics_data":false,"financial_data":false,"legal_info":false}}',
            ]
        );
    }

    /**
     * @covers ::list
     */
    public function testListFullFilter(): void
    {
        $this->quickTest(
            'list',
            [
                [
                    'filter' => [
                        'since'           => new \DateTime('2026-08-01T00:00:00+00:00'),
                        'to'              => new \DateTime('2026-08-08T00:00:00+00:00'),
                        'statuses'        => ['awaiting_packaging', 'delivering'],
                        'order_numbers'   => ['33920474-0032'],
                        'posting_numbers' => ['33920474-0032-1'],
                        // must be filtered out
                        'foo'             => 'bar',
                    ],
                    'cursor'   => 'cursor-from-previous-response',
                    'limit'    => 100,
                    'sort_dir' => 'desc',
                    'translit' => false,
                    'with'     => ['analytics_data' => true, 'legal_info' => true],
                ],
            ],
            [
                'POST',
                '/v3/posting/fbo/list',
                '{"filter":{"since":"2026-08-01T00:00:00+00:00","to":"2026-08-08T00:00:00+00:00","statuses":["awaiting_packaging","delivering"],"order_numbers":["33920474-0032"],"posting_numbers":["33920474-0032-1"]},"cursor":"cursor-from-previous-response","limit":100,"sort_dir":"desc","translit":false,"with":{"analytics_data":true,"financial_data":false,"legal_info":true}}',
            ]
        );
    }

    /**
     * The response has no `result` wrapper, so it is returned as is.
     *
     * @covers ::list
     */
    public function testListResponse(): void
    {
        $json = <<<JSON
{
  "cursor": "next-cursor",
  "has_next": true,
  "postings": [
    {
      "order_id": 354679434,
      "order_number": "33920474-0032",
      "posting_number": "33920474-0032-1",
      "status": "delivered",
      "products": [
        {
          "sku": 160249683,
          "name": "Так говорил Заратустра",
          "quantity": 1,
          "offer_id": "9789785079999",
          "price": "1000.00"
        }
      ]
    }
  ]
}
JSON;

        $this->quickTest(
            'list',
            [
                [
                    'filter' => [
                        'since' => '2026-08-01T00:00:00+00:00',
                        'to'    => '2026-08-08T00:00:00+00:00',
                    ],
                ],
            ],
            [
                'POST',
                '/v3/posting/fbo/list',
                '{"filter":{"since":"2026-08-01T00:00:00+00:00","to":"2026-08-08T00:00:00+00:00"},"cursor":"","limit":10,"sort_dir":"asc","translit":true,"with":{"analytics_data":false,"financial_data":false,"legal_info":false}}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }
}
